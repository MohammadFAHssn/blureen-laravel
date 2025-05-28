<?php

namespace App\Services\Api;

use Illuminate\Support\Str;

class RayvarzService
{
    public function sync($request)
    {
        $modelName = Str::studly($request->query('model'));

        return $modelName;
    }
}
