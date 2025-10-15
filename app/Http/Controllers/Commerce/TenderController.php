<?php

namespace App\Http\Controllers\Commerce;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Request;
use App\Exceptions\CustomException;
use Illuminate\Support\Facades\Http;

class TenderController
{
    /**
     * @throws CustomException
     * @throws ConnectionException
     */
    public function getByToken(Request $request)
    {
        $token = $request->query('token');

        $response = Http::withoutVerifying()->withHeaders([
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ])->get(
                config('services.legacy_integrated_system.get_tender_by_token') . $token
            );

        if ($response->failed()) {
            throw new CustomException($response['message'], 403);
        }
        return $response->json();
    }

    /**
     * @throws CustomException
     * @throws ConnectionException
     */
    public function getActives(Request $request)
    {
        $supplierId = $request->query('supplier_id');

        $response = Http::withoutVerifying()->withHeaders([
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ])->get(
                config('services.legacy_integrated_system.get_active_tenders') . $supplierId
            );

        if ($response->failed()) {
            throw new CustomException($response['message'], 500);
        }
        return $response->json();
    }

    /**
     * @throws CustomException
     * @throws ConnectionException
     */
    public function submitBid(Request $request)
    {
        $response = Http::withoutVerifying()->withHeaders([
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ])->post(
                config('services.legacy_integrated_system.submit_bid'),
                $request->all()
            );

        if ($response->failed()) {
            throw new CustomException($response['message'], 500);
        }
        return $response->json();
    }
}
