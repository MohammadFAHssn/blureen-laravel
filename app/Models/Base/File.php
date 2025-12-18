<?php

namespace App\Models\Base;

use App\Models\User;
use Database\Factories\Base\FileFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class File extends Model
{
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'original_name',
        'stored_name',
        'path',
        'disk',
        'mime_type',
        'extension',
        'size',
        'fileable_type',
        'fileable_id',
        'collection',
        'title',
        'description',
        'metadata',
        'visibility',
        'expires_at',
        'uploaded_by',
        'order',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'metadata' => 'array',
            'size' => 'integer',
            'order' => 'integer',
            'expires_at' => 'datetime',
        ];
    }
}
