<?php

namespace App\Models\Payroll;

use Illuminate\Support\Facades\Crypt;
use Illuminate\Database\Eloquent\Model;

class PayrollItem extends Model
{
    protected $fillable = [
        'payroll_slip_id',
        'item_title',
        'item_value'
    ];

    public function setItemValueAttribute($value)
    {
        $this->attributes['item_value'] = Crypt::encryptString((string) $value);
    }

    public function getItemValueAttribute($value)
    {
        $decrypted = Crypt::decryptString($value);

        // Preserve strings with leading zeros (e.g., "01", "000123")
        if (preg_match('/^0\d+$/', $decrypted)) {
            return $decrypted;
        }

        // Convert numeric strings to appropriate numeric types
        if (is_numeric($decrypted)) {
            return str_contains($decrypted, '.') ? (float) $decrypted : (int) $decrypted;
        }

        return $decrypted;
    }
}
