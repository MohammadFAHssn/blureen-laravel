<?php

namespace App\Models\Birthday;

use App\Models\User;
use App\Models\Birthday\BirthdayFile;
use App\Models\Birthday\BirthdayGift;
use Illuminate\Database\Eloquent\Model;

class BirthdayFileUser extends Model
{
    protected $table = 'birthday_files_users';

    protected $fillable = [
        'birthday_file_id',
        'user_id',
        'birthday_gift_id',
        'status',
        'created_by',
        'edited_by',
    ];

    public function birthdayFile()
    {
        return $this->belongsTo(BirthdayFile::class, 'birthday_file_id');
    }

    public function birthdayGift()
    {
        return $this->belongsTo(BirthdayGift::class, 'birthday_gift_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function editedBy()
    {
        return $this->belongsTo(User::class, 'edited_by');
    }
}
