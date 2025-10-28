<?php

namespace App\Services\Base;

use Illuminate\Support\Str;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;
class BaseService
{

    public function get($request)
    {
        $params = $request->route()->parameters();

        $module = Str::studly($params['module']);
        $modelName = Str::studly($params['model_name']);

        if ($modelName === 'User') {
            $modelClass = 'App\\Models\\User';
        } else {
            $modelClass = 'App\\Models\\' . $module . '\\' . $modelName;
        }

        $filterKeys = array_keys($request->query('filter', []));
        $filter = array_map(fn($key) => AllowedFilter::exact($key), $filterKeys);

        $include = $request->query('include', '');
        $arrayedInclude = explode(',', $include);

        $fields = $request->query('fields', []);
        $relations = array_keys($fields);

        $allowedFields = [];
        foreach ($relations as $relation) {
            $fieldsOfRelation = explode(',', $fields[$relation]);
            foreach ($fieldsOfRelation as $field) {
                if ($relation === $params['model_name'] . 's') {
                    $allowedFields[] = $field;
                } else {
                    $allowedFields[] = $relation . '.' . $field;
                }
            }
        }

        return QueryBuilder::for($modelClass)
            ->allowedFilters($filter)
            ->allowedFields($allowedFields)
            ->allowedIncludes($arrayedInclude)
            ->get();
    }

    public function delete($request)
    {
        $segments = $request->segments();

        $module = Str::studly($segments[count($segments) - 2]);
        $modelName = Str::studly($segments[count($segments) - 1]);

        $modelClass = 'App\\Models\\' . $module . '\\' . $modelName;

        foreach ($request->ids as $id) {
            $modelClass::find($id)->delete();
        }
    }
}
