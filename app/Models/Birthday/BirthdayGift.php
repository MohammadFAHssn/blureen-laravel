<?php

namespace App\Models\Birthday;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;

class BirthdayGift extends Model
{
    protected $fillable = [
        'name',
        'code',
        'image',
        'status',
        'amount',
        'created_by',
        'edited_by',
    ];

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function editedBy()
    {
        return $this->belongsTo(User::class, 'edited_by');
    }

    protected static function booted()
    {
        static::deleting(function ($gift) {
            DB::table('birthday_files_users')
                ->where('birthday_gift_id', $gift->id)
                ->update(['birthday_gift_id' => 0]);
        });
    }
}
