<?php

namespace App\Services;

use Spatie\QueryBuilder\QueryBuilder;
use Illuminate\Support\Str;

class BaseService
{
    public static function get($request)
    {
        $modelDir = $request->segment(2);
        $modelName = Str::studly($request->segment(3));

        if ($modelDir === "base") {
            $modelClass = 'App\\Models\\' . $modelName;
        } else {
            $modelClass = 'App\\Models\\' . Str::studly($modelDir) . '\\' . $modelName;
        }

        return QueryBuilder::for($modelClass)->get();
    }
}
