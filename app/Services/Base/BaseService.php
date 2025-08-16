<?php

namespace App\Services\Base;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;
use App\Repositories\Base\BaseRepository;

class BaseService
{
    protected $baseRepository;

    public function __construct()
    {
        $this->baseRepository = new BaseRepository;
    }

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

        $filter = array_keys(
            array_map(function ($item) {
                return AllowedFilter::exact($item);
            }, $request->query('filter', []))
        );

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

        Log::info('Fetching data for model: ' . $modelClass, [
            'filters' => $filter,
            'includes' => $arrayedInclude,
            'fields' => $allowedFields,
        ]);

        return QueryBuilder::for($modelClass)
            ->allowedFilters($filter)
            ->allowedFields($allowedFields)
            ->allowedIncludes($arrayedInclude)
            ->get();
    }
}
