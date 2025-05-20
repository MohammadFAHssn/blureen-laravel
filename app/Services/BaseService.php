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

        $filter = array_keys($request->query('filter', []));

        $include = $request->query('include', "");
        $arrayedInclude = explode(',', $include);

        $fields = $request->query('fields', []);
        $relations = array_keys($fields);

        $allowedFields = [];
        foreach ($relations as $relation) {
            $fieldsOfRelation = explode(',', $fields[$relation]);
            foreach ($fieldsOfRelation as $field) {
                $allowedFields[] = $relation . '.' . $field;
            }
        }

        return QueryBuilder::for($modelClass)
            ->allowedFilters($filter)
            ->allowedFields($allowedFields)
            ->allowedIncludes($arrayedInclude)
            ->get();
    }
}
