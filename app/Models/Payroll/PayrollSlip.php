<?php

namespace App\Models\Payroll;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class PayrollSlip extends Model
{
    protected $fillable = [
        'batch_id',
        'user_id',
    ];

    public function payrollBatch()
    {
        return $this->belongsTo(PayrollBatch::class, 'batch_id');
    }

    public function payrollItems()
    {
        return $this->hasMany(PayrollItem::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
