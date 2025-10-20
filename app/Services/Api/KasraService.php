<?php

namespace App\Services\Api;

use App\Jobs\SyncWithKasraJob;
use Exception;
use Illuminate\Support\Facades\DB;
use App\Exceptions\CustomException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Throwable;

class KasraService
{
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
    public function modifyCredit(array $payload, string $kasraBaseUrl, array $opts = []): array
    {
        $endpoint = rtrim($kasraBaseUrl, '/') . '/KasraManageCredit/ManageCredit.asmx';

        $saveXmlInner = $this->buildSaveXml($payload);

        $includeAction = (bool)($opts['include_action_in_content_type'] ?? false);
        [$headers, $envelope] = $this->buildSoap12Envelope($saveXmlInner, $includeAction);

        if (!empty($opts['extra_headers']) && is_array($opts['extra_headers'])) {
            $headers = array_merge($headers, $opts['extra_headers']);
        }

        try {
            $response = Http::timeout(20)
                ->withHeaders($headers)
                ->withBody($envelope, $headers['Content-Type'] ?? 'application/soap+xml; charset=utf-8')
                ->post($endpoint);

            if ($response->failed()) {
                Log::error('Kasra ModifyCredit failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                throw new Exception('تماس SOAP با سرویس کسرا در ModifyCredit با خطا مواجه شد.', 502);
            }

            $body = $response->body();
            $parsed = $this->xmlToArraySafe($body);

            Log::info('Kasra ModifyCredit done', [
                'status' => $response->status(),
            ]);

            return [
                'status' => $response->status(),
                'raw_xml' => $body,
                'parsed' => $parsed,
            ];
        } catch (Throwable $e) {
            Log::error('Kasra ModifyCredit exception', [
                'error' => $e->getMessage(),
            ]);
            throw new CustomException('عدم دسترسی یا خطا در فراخوانی سرویس کسرا (ModifyCredit).', 500);
        }
    }

    protected function buildSaveXml(array $credit): string
    {
        $defaults = [
            'PersonCode' => '',
            'StartDate' => '',
            'EndDate' => '',
            'StartTime' => '',
            'EndTime' => '',
            'Description' => '',
            'CreditType' => '',
            'CreditID' => '0',
        ];
        $d = array_merge($defaults, $credit);

        return <<<XML
<Root>
  <Credit>
    <PersonCode>{$d['PersonCode']}</PersonCode>
    <StartDate>{$d['StartDate']}</StartDate>
    <EndDate>{$d['EndDate']}</EndDate>
    <StartTime>{$d['StartTime']}</StartTime>
    <EndTime>{$d['EndTime']}</EndTime>
    <Description>{$d['Description']}</Description>
    <CreditType>{$d['CreditType']}</CreditType>
    <CreditID>{$d['CreditID']}</CreditID>
  </Credit>
</Root>
XML;
    }

    protected function buildSoap12Envelope(string $saveXmlInner, bool $includeActionInContentType = false): array
    {
        $contentType = 'application/soap+xml; charset=utf-8';
        if ($includeActionInContentType) {
            $contentType = 'application/soap+xml; charset=utf-8; action="http://Kasra.org/ModifyCredit"';
        }

        $headers = [
            'Content-Type' => $contentType,
        ];

        $xml = <<<XML
<?xml version="1.0" encoding="utf-8"?>
<soap12:Envelope
                 xmlns:soap12="http://www.w3.org/2003/05/soap-envelope">
  <soap12:Body>
    <ModifyCredit xmlns="http://Kasra.org/">
      <SaveXml><![CDATA[
        {$saveXmlInner}
      ]]></SaveXml>
    </ModifyCredit>
  </soap12:Body>
</soap12:Envelope>
XML;

        return [$headers, $xml];
    }

    protected function xmlToArraySafe(string $xml): ?array
    {
        libxml_use_internal_errors(true);
        $simple = simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA);
        if ($simple === false) {
            return null;
        }
        $json = json_encode($simple);
        return $json ? json_decode($json, true) : null;
    }

}
