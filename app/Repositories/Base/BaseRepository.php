<?php

namespace App\Repositories\Base;

class BaseRepository
{
    public function getByFiltersWithRelations($model, $filters, $relations)
    {
        return $model::where($filters)->with($relations);
    }
}
