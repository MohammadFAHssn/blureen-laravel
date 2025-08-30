<?php

namespace App\Models\Payroll;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class PayrollBatch extends Model
{
    protected $fillable = [
        'month',
        'year',
        'uploaded_by',
        'filename',
        'sms_sent',
    ];

    public function uploadedBy()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    protected $appends = ['month_name'];

    protected function monthName(): Attribute
    {
        return new Attribute(
            get: fn() => getJalaliMonthNameByIndex($this->month)
        );
    }
}
