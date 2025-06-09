<?php

namespace App\Jobs;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendOtpSmsJob implements ShouldQueue
{
    use Queueable;

    protected int $otpCode;
    protected string $mobileNumber;

    /**
     * Create a new job instance.
     */
    public function __construct(int $otpCode, string $mobileNumber)
    {
        $this->otpCode = $otpCode;
        $this->mobileNumber = $mobileNumber;

        $this->queue = 'sms';
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $response = Http::withOptions(['verify' => false])->withHeaders([
            'Authorization' => env('SMS_PISHGAMRAYAN_TOKEN'),
            'Content-Type' => 'application/json',
        ])->post(
                'https://smsapi.pishgamrayan.com/Messages/SendOtp',
                [
                    'otpId' => 100276,
                    'parameters' => [$this->otpCode],
                    'senderNumber' => '500032568500',
                    'recipientNumbers' => [$this->mobileNumber]
                ],
            );

        Log::info($response);
    }
}
