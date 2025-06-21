<?php

namespace App\Http\Controllers\Api;

use App\Services\Api\RayvarzService;
use Illuminate\Http\Request;

class RayvarzController
{
    protected $rayvarzService;

    public function __construct()
    {
        $this->rayvarzService = new RayvarzService();
    }

    public function sync(Request $request)
    {
        return response()->json(['data' => $this->rayvarzService->sync($request)], 200);
    }
}
