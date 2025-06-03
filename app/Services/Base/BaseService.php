<?php

namespace App\Services\Base;

use App\Repositories\Base\BaseRepository;
use Spatie\QueryBuilder\QueryBuilder;
use Illuminate\Support\Str;

class BaseService
{
    protected $baseRepository;

    public function __construct()
    {
        $this->baseRepository = new BaseRepository();
    }

    public function get($request)
    {
        $model = $request->query('model');
        $explodedModel = explode(".", $model);
        $modelDir = Str::studly($explodedModel[0]);
        $modelName = Str::studly($explodedModel[1]);

        if ($modelDir === "Base") {
            $modelClass = 'App\\Models\\' . $modelName;
        } else {
            $modelClass = 'App\\Models\\' . $modelDir . '\\' . $modelName;
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

    public function getByFiltersWithRelations($model, $filters, $with)
    {
        return $this->baseRepository->getByFiltersWithRelations($model, $filters, $with);
    }
}
