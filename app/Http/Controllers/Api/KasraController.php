<?php

namespace App\Http\Controllers\Api;

use App\Services\Api\KasraService;

class KasraController
{
    protected $kasraService;

    public function __construct(KasraService $kasraService)
    {
        $this->kasraService = $kasraService;
    }

    public function sync()
    {
        return response()->json(['data' => $this->kasraService->sync()], 200);
    }
}
