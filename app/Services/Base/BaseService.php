<?php

namespace App\Services\Base;

use App\Repositories\Base\BaseRepository;
use Illuminate\Support\Str;
use Spatie\QueryBuilder\QueryBuilder;

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

        if ($module === 'Base') {
            $modelClass = 'App\\Models\\' . $modelName;
        } else {
            $modelClass = 'App\\Models\\' . $module . '\\' . $modelName;
        }

        $filter = array_keys($request->query('filter', []));

        $include = $request->query('include', '');
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
