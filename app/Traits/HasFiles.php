<?php

namespace App\Traits;

use App\Models\Base\File;
use Illuminate\Database\Eloquent\Relations\MorphOne;

trait HasFiles
{
    public function avatar(): MorphOne
    {
        return $this->morphOne(File::class, 'fileable')
            ->where('collection', 'avatar')
            ->latest();
    }
}
