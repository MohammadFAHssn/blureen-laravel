<?php

namespace App\Repositories\Base;

use App\Models\Base\File;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Storage;

class FileRepository
{

    // Delete file from storage and database permanently
    public function forceDelete(File $file): bool
    {
        Storage::disk($file->disk)->delete($file->path);
        return $file->forceDelete();
    }

    // get files of a model
    public function getByFileable(string $fileableType, int $fileableId, ?string $collection = null): Collection
    {
        $query = File::where('fileable_type', $fileableType)
            ->where('fileable_id', $fileableId);

        if ($collection) {
            $query->where('collection', $collection);
        }

        return $query->orderBy('order')->get();
    }
}
