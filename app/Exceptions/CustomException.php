<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Support\Facades\Log;

class CustomException extends Exception
{
    public function report(): void
    {
        Log::error($this->getMessage());
    }

    public function render()
    {
        return response()->json([
            'message' => $this->getMessage()
        ], $this->getCode() === 0 ? 500 : $this->getCode());
    }
}
