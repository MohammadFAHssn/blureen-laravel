<?php

namespace App\Http\Controllers\Commerce;

use Illuminate\Http\Request;
use App\Exceptions\CustomException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TenderController
{
    public function getByToken(Request $request)
    {
        $token = $request->query('token');

        Log::info('Calling URL: ' . config('services.legacy_integrated_system.get_tender_by_token') . $token);

        $response = Http::withHeaders([
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ])->get(
                config('services.legacy_integrated_system.get_tender_by_token') . $token
            );

        if ($response->failed()) {
            throw new CustomException($response['errors']['token'][0], 403);
        }
        return $response->json();
    }

    public function getActives(Request $request)
    {
        $supplierId = $request->query('supplier_id');

        $response = Http::withHeaders([
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ])->get(
                config('services.legacy_integrated_system.get_active_tenders') . $supplierId
            );

        if ($response->failed()) {
            throw new CustomException('خطایی رخ داده‌است.', 500);
        }
        return $response->json();
    }

    public function submitBid(Request $request)
    {
        $response = Http::withHeaders([
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ])->post(
                config('services.legacy_integrated_system.submit_bid'),
                $request->all()
            );

        if ($response->failed()) {
            throw new CustomException('خطا در ارسال پیشنهاد.', 500);
        }
        return $response->json();
    }
}
