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
            //'PersonCode' => $hrRequest->user->personnel_code,
            'PersonCode' => '123456789',
            'StartDate' => $hrRequest->start_date,
            'EndDate' => $hrRequest->end_date,
            'StartTime' => $hrRequest->start_time ?? '',
            'EndTime' => $hrRequest->end_time??'',
            'Description' => $hrRequest->description ?? '',
            'CreditType' => $this->creditTypeMap[$hrRequest->request_type_id],
            'CreditID' => '0',
        ];


        $endpoint = config('services.kasra.modify_credit_url');
        $headers = ['Content-Type' => 'application/soap+xml; charset=utf-8'];
        $envelope = $this->generateRequestXml($requestData);

        try {
            $response = Http::timeout(30)
                ->withBody($envelope, $headers['Content-Type'])
                ->post($endpoint);

            if ($response->failed()) {
                Log::error('Kasra ModifyCredit failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                throw new Exception('SOAP call failed for ModifyCredit.');
            }

            $body = $response->body();
            return [
                'status' => $response->status(),
                'raw_xml' => $body,
            ];
        } catch (Throwable $e) {
            Log::error('Kasra ModifyCredit exception', ['error' => $e->getMessage()]);
            throw new CustomException('عدم دسترسی یا خطا در فراخوانی سرویس کسرا', 500);
        }
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



}
