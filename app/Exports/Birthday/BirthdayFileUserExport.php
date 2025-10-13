<?php

namespace App\Exports\Birthday;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class BirthdayFileUserExport implements FromCollection, WithHeadings, WithStyles
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function collection()
    {
        return collect($this->data)->map(function ($item) {
            return [
                'نام' => $item['user']['first_name'] ?? 'یافت نشد',
                'نام خانوادگی' => $item['user']['last_name'] ?? 'یافت نشد',
                'کد پرسنلی' => $item['user']['personnel_code'] ?? 'یافت نشد',
                'نام هدیه' => $item['gift']['name'] ?? 'انتخاب نشده',
                'کد هدیه' => $item['gift']['code'] ?? 'انتخاب نشده',
            ];
        });
    }

    public function headings(): array
    {
        return [
            'نام',
            'نام خانوادگی',
            'کد پرسنلی',
            'نام هدیه',
            'کد هدیه',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->setRightToLeft(true);

        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
