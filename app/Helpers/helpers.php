<?php

use Morilog\Jalali\Jalalian;
use Illuminate\Support\Facades\Log;

if (!function_exists('getJalaliMonthNameByIndex')) {
    function getJalaliMonthNameByIndex($index)
    {
        $jalaliMonthNames = [
            1 => 'فروردین',
            2 => 'اردیبهشت',
            3 => 'خرداد',
            4 => 'تیر',
            5 => 'مرداد',
            6 => 'شهریور',
            7 => 'مهر',
            8 => 'آبان',
            9 => 'آذر',
            10 => 'دی',
            11 => 'بهمن',
            12 => 'اسفند',
        ];

        return $jalaliMonthNames[$index] ?? null;
    }

    function arabicToPersian(array $records): array
    {
        Log::info('Converting Arabic characters to Persian', [
            'recordCount' => count($records),
        ]);

        $search = ['ي', 'ك'];
        $replace = ['ی', 'ک'];

        return array_map(function ($record) use ($search, $replace) {
            return array_map(function ($value) use ($search, $replace) {
                return is_string($value) ? str_replace($search, $replace, $value) : $value;
            }, $record);
        }, $records);
    }

    function jalalianYmdDateToCarbon($jalalianYmdDate)
    {
        if (empty($jalalianYmdDate)) {
            return null;
        }
        try {
            return Jalalian::fromFormat('Ymd', $jalalianYmdDate)->toCarbon();
        } catch (\Throwable $e) {
            Log::error('Error converting Jalalian Ymd Date to Carbon', [
                'jalalianYmdDate' => $jalalianYmdDate,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }
}
