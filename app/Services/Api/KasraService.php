<?php

namespace App\Services\Api;

use App\Constants\AppConstants;
use App\Jobs\SyncWithKasraJob;
use App\Models\HrRequest\HrRequest;
use Exception;
use Illuminate\Support\Facades\DB;
use App\Exceptions\CustomException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Throwable;

class KasraService
{
    //mapping request_type_id with kasra creditType
    protected array $creditTypeMap = [
        AppConstants::HR_REQUEST_TYPE_DAILY_LEAVE => '11002', // مرخصی روزانه
        AppConstants::HR_REQUEST_TYPE_HOURLY_LEAVE => '11001', // مرخصی ساعتی
        AppConstants::HR_REQUEST_TYPE_OVERTIME => '11101', // اضافه کار عادی
    ];

    public function sync(): void
    {
        SyncWithKasraJob::dispatch();
    }

    /**
     * @throws CustomException
     */
    public function fetchUsers()
    {
        Log::info('Fetching users from Kasra');

        try {
            $response = Http::timeout(60)->withHeaders([
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ])->post(
                config('services.kasra.fetch.users'),
            );

            if ($response->failed()) {
                Log::error('Error fetching users from Kasra', [
                    'response' => $response,
                ]);
                throw new CustomException('هنگام دریافت اطلاعات کاربران از کسرا خطایی رخ داده‌است.', 500);
            }

            return $response->json();
        } catch (Exception $e) {
            Log::error('Error fetching users from Kasra', [
                'error' => $e->getMessage(),
            ]);
            throw new CustomException('هنگام دریافت اطلاعات کاربران از کسرا خطایی رخ داده‌است.', 500);
        }
    }

    /**
     * @throws CustomException
     */
    public function syncUsers(): void
    {
        $users = $this->fetchUsers();

        $users = arabicToPersian($users['data']);

        Log::info('Syncing users to database', [
            'userCount' => count($users),
        ]);

        $userData = [];
        foreach ($users as $user) {

            if (strlen($user['Code']) !== 4) {
                continue;
            }

            $userData[] = [
                'first_name' => $user['FName'],
                'last_name' => $user['LName'],
                'username' => $user['Code'],
                'personnel_code' => $user['Code'],
                'active' => false,
                'updated_at' => now(),
            ];
        }

        foreach (array_chunk($userData, 500) as $chunk) {
            DB::table('users')->upsert($chunk, ['personnel_code']);
        }

        Log::info('Sync completed');
    }

    /**
     * @throws CustomException
     */
    public function modifyCredit(HrRequest $hrRequest): array
    {
        $requestData = [
            'PersonCode'  => '123456789',
            'StartDate'   => $hrRequest->start_date,
            'EndDate'     => $hrRequest->end_date,
            'StartTime'   => $hrRequest->start_time ?? '',
            'EndTime'     => $hrRequest->end_time ?? '',
            'Description' => $hrRequest->description ?? '',
            'CreditType'  => $this->creditTypeMap[$hrRequest->request_type_id],
            'CreditID'    => '0',
        ];


        $endpoint = config('services.kasra.modify_credit_url');
        $envelope = $this->generateRequestXml($requestData);

        try {
            $response = Http::timeout(30)
                ->withBody($envelope, 'application/soap+xml; charset=utf-8')
                ->post($endpoint);

            return $this->parseModifyCreditResponse($response->body());
        } catch (Throwable $e) {
            Log::error('Kasra ModifyCredit exception', ['error' => $e->getMessage()]);
            throw new CustomException('عدم دسترسی یا خطا در فراخوانی سرویس کسرا', 500);
        }
    }

    /**
     * @throws CustomException
     */
    private function parseModifyCreditResponse(string $soapBody): array
    {
        libxml_use_internal_errors(true);
        $body = trim($soapBody);
        $body = preg_replace('/^\xEF\xBB\xBF|[^\x09\x0A\x0D\x20-\x7E\x{80}-\x{10FFFF}]/u', '', $body);
        $xml = simplexml_load_string($body);
        if ($xml === false) {
            throw new CustomException('XML نامعتبر از سرویس کسرا', 500);
        }
        $nodes = $xml->xpath('//*[local-name()="ModifyCreditResult"]');
        if (!$nodes || !isset($nodes[0])) {
            throw new CustomException('ساختار پاسخ کسرا نامعتبر است', 500);
        }
        $inner = html_entity_decode((string)$nodes[0], ENT_QUOTES | ENT_XML1, 'UTF-8');
        $innerXml = simplexml_load_string($inner, 'SimpleXMLElement', LIBXML_NOCDATA);
        if ($innerXml === false) {
            throw new CustomException('XML داخلی پاسخ کسرا نامعتبر است', 500);
        }

        return [
            'success' => ((int)($innerXml->Validate ?? 0) === 1),
            'message'  => (string)$innerXml->Message  ?? 'خطای ناشناخته هنگام ثبت در کسرا',
            'creditID' => (string)$innerXml->CreditID ?? null,
        ];

    }

    protected function generateRequestXml(array $data): string
    {
        return <<<XML
            <soap12:Envelope xmlns:soap12="http://www.w3.org/2003/05/soap-envelope">
              <soap12:Body>
                <ModifyCredit xmlns="http://Kasra.org/">
                  <SaveXml><![CDATA[
                  <Root>
                    <Credit>
                        <PersonCode>{$data['PersonCode']}</PersonCode>
                        <StartDate>{$data['StartDate']}</StartDate>
                        <EndDate>{$data['EndDate']}</EndDate>
                        <StartTime>{$data['StartTime']}</StartTime>
                        <EndTime>{$data['EndTime']}</EndTime>
                        <Description>{$data['Description']}</Description>
                        <CreditType>{$data['CreditType']}</CreditType>
                        <CreditID>{$data['CreditID']}</CreditID>
                    </Credit>
                  </Root>
                  ]]></SaveXml>
                </ModifyCredit>
              </soap12:Body>
            </soap12:Envelope>
        XML;
    }

    public function getEmployeeAttendanceReport($data): array
    {
        $personnelCode = $data['personnel_code'];
        $startDate = $data['start_date'];
        $endDate = $data['end_date'];

        $xmlParam = "
        <ReportEntity>
            <Tb>
                <Caption>پرسنلي</Caption>
                <Value>$personnelCode</Value>
            </Tb>
            <Tb>
                <Caption>از تاريخ</Caption>
                <Value>$startDate</Value>
            </Tb>
            <Tb>
                <Caption>تا تاريخ</Caption>
                <Value>$endDate</Value>
            </Tb>
        </ReportEntity>";

        $endpoint = config('services.kasra.get_report_url');

        $form = [
            'outPutType'              => 1,
            'OnLineUserID'            => $personnelCode,
            'ReportID'                => AppConstants::KASRA_REPORTS['ATTENDANCE_LOGS'],
            'XmlParam'                => $xmlParam,
            'PageNumber'              => 1,
            'PageSize'                => 0,
            'CompanyFinatialPeriodID' => 1,
        ];

        $response = Http::asForm()
            ->withHeaders([
                'Accept' => 'text/xml, application/xml, */*',
            ])
            ->timeout(120)
            ->post($endpoint, $form);

        if (!$response->ok()) {
            throw new RuntimeException("Kasra service error: HTTP {$response->status()}");
        }

        $raw     = $response->body();
        $retXml  = null;   // برای سازگاری؛ این سرویس عملاً xml جدا ندارد
        $retDs   = null;   // XML کامل نود <ds>
        $rows    = [];     // آرایه‌ی ردیف‌ها (GetShowReport)
        $total   = null;   // TotalRecords (در صورت وجود)
        $page    = null;   // PageNumber (در صورت وجود)

        // کمکی: دی‌کد کردن اسامی فیلد مانند _x0020_ → فاصله
        $decodeXmlEncodedName = static function (string $name): string {
            return preg_replace_callback('/_x([0-9A-Fa-f]{4})_/', function ($m) {
                return mb_convert_encoding('&#x'.$m[1].';', 'UTF-8', 'HTML-ENTITIES');
            }, $name);
        };

        try {
            $sx = @simplexml_load_string($raw);
            if ($sx === false) {
                return [
                    'raw'     => $raw,
                    'ret_xml' => $retXml,
                    'ret_ds'  => $retDs,
                    'parsed'  => $rows,
                    'total'   => $total,
                    'page'    => $page,
                ];
            }

            // ثبت فضاهای نام
            $sx->registerXPathNamespace('t', 'http://tempuri.org/');
            $sx->registerXPathNamespace('d', 'urn:schemas-microsoft-com:xml-diffgram-v1');

            // متن کامل نود <ds> (برای دیباگ/ذخیره)
            $dsNodes = $sx->xpath('//t:ds');
            if (!empty($dsNodes)) {
                // asXML شامل خود تگ <ds> و محتوای داخل آن است
                $retDs = $dsNodes[0] instanceof SimpleXMLElement ? $dsNodes[0]->asXML() : null;
            }

            // لیست ردیف‌ها در diffgram → ReportEntity → GetShowReport
            $getShowReportNodes = $sx->xpath('//t:ds/d:diffgram/*[local-name()="ReportEntity"]/*[local-name()="GetShowReport"]');

            if (!empty($getShowReportNodes)) {
                foreach ($getShowReportNodes as $node) {
                    $item = [];

                    // خواندن همه‌ی فیلدهای فرزند
                    foreach ($node->children() as $child) {
                        $key   = $decodeXmlEncodedName($child->getName()); // مانند "كد_x0020_پرسنلي" ← "كد پرسنلي"
                        $key   = trim(preg_replace('/\s+/u', ' ', $key));
                        $value = trim((string) $child);

                        // تبدیل فاصله/نیم‌فاصله به _
                        $slug = str_replace([' ', '‌'], '_', $key);
                        // حذف کاراکترهای ناخواسته از کلید
                        $slug = preg_replace('/[^\p{L}\p{N}_]/u', '', $slug);

                        $item[$slug] = $value;
                    }

                    // استخراج total/page از هر ردیف (اگر موجود باشد)
                    if (isset($item['TotalRecords']) && $total === null) {
                        $total = $item['TotalRecords'];
                    }
                    if (isset($item['PageNumber']) && $page === null) {
                        $page = $item['PageNumber'];
                    }

                    $rows[] = $item;
                }
            }
        } catch (Throwable $e) {
            // در صورت خطای پارس، فقط raw و ds را برمی‌گردانیم
        }

        return [
            'raw'     => $raw,   // متن کامل پاسخ ASMX
            'ret_xml' => $retXml,
            'ret_ds'  => $retDs, // XML کامل <ds> (برای بررسی/دیباگ)
            'parsed'  => $rows,  // آرایه‌ی تمیز از ردیف‌های GetShowReport
            'total'   => $total, // مجموع رکوردها (در صورت وجود)
            'page'    => $page,  // شماره صفحه (در صورت وجود)
        ];
    }



}
