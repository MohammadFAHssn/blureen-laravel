<?php

namespace App\Models\Payroll;

use Illuminate\Database\Eloquent\Model;

class PayrollBatch extends Model
{
    protected $fillable = [
        'month',
        'year',
        'uploaded_by',
        'filename',
        'sms_sent',
    ];
}
