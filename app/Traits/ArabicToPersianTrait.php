<?php

namespace App\Traits;

trait ArabicToPersianTrait
{
    protected function arabicToPersian($value)
    {
        return is_string($value)
            ? strtr($value, [
                'ي' => 'ی',
                'ك' => 'ک',
            ])
            : $value;
    }

    public function getAttribute($key)
    {
        $value = parent::getAttribute($key);
        return $this->arabicToPersian($value);
    }
}
