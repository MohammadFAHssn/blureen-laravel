<?php
namespace App\Services\Payroll;

use App\Repositories\Payroll\PayrollSlipRepository;
use Spatie\LaravelPdf\Facades\Pdf;

class PayrollSlipService
{
    protected $payrollSlipRepository;

    public function __construct()
    {
        $this->payrollSlipRepository = new PayrollSlipRepository;
    }

    public function getTheLastFewMonths($request)
    {
        $month = $request->query('month', '');
        $year = $request->query('year', '');
        $last = $request->query('last', '');

        return $this->payrollSlipRepository->getTheLastFewMonths($month, $year, $last);
    }

    public function getReports($request)
    {
        return $this->payrollSlipRepository->getReports($request);
    }

    public function print($request)
    {
        $month = $request->query('month');
        $year = $request->query('year');
        $monthName = getJalaliMonthNameByIndex($month);

        $user = auth()->user();

        $costCenter = $user->profile?->costCenter?->name;

        $payrollSlip = $this->payrollSlipRepository->getTheLastFewMonths($month, $year, 1);
        $payrollItems = collect($payrollSlip[0]->toArray()['payroll_items'])->keyBy('item_title');
        $payrollValue = fn($key) => $payrollItems->get($key)['item_value'] ?? null;

        $employeeInfo = [
            ['label' => 'شماره پرسنلی', 'value' => $payrollValue('پرسنلی')],
            ['label' => 'نام', 'value' => $payrollValue('نام')],
            ['label' => 'نام خانوادگی', 'value' => $payrollValue('نام خانوادگی')],
            ['label' => 'نام پدر', 'value' => $user->profile?->father_name],
            ['label' => 'شماره بیمه', 'value' => $payrollValue('شماره بیمه')],
            ['label' => 'مرکز هزینه', 'value' => $costCenter],
            ['label' => 'گروه', 'value' => $payrollValue('گروه')],
            ['label' => 'پایه', 'value' => $payrollValue('پایه')],
            ['label' => 'رتبه', 'value' => $payrollValue('رتبه')],
        ];

        $payments = [
            ['label' => 'حقوق پایه', 'amount' => $payrollValue('حقوق پايه')],
            ['label' => 'حق اولاد', 'amount' => $payrollValue('حق اولاد')],
            ['label' => 'حق مسکن', 'amount' => $payrollValue('حق مسكن')],
            ['label' => 'خواربار', 'amount' => $payrollValue('خواربار')],
            ['label' => 'حق تأهل', 'amount' => $payrollValue('حق تاهل')],
            ['label' => 'نوبت کاری 10%', 'amount' => $payrollValue('نوبتكاری 10%')],
            ['label' => 'نوبت کاری 15%', 'amount' => $payrollValue('نوبتكاری 15%')],
            ['label' => 'بن کارگری', 'amount' => $payrollValue('بن كارگری')],
            ['label' => 'پایه سنوات سالیانه', 'amount' => $payrollValue('پايه سنوات ساليانه')],
            ['label' => 'مزد سنوات', 'amount' => $payrollValue('مزد سنوات')],
            ['label' => 'حق پست', 'amount' => $payrollValue('حق پست')],
            ['label' => 'ماندگاری پست', 'amount' => $payrollValue('ماندگاری پست')],
            ['label' => 'ماندگاری محیط کار (سختی کار)', 'amount' => $payrollValue('ماندگاری محيط كار(سختی كار)')],
            ['label' => 'محیط کار (سختی کار)', 'amount' => $payrollValue('محيط كار(سختی كار)')],
            ['label' => 'پاداش مدیریت', 'amount' => $payrollValue('پاداش مدیریت')],
            ['label' => 'ماندگاری پاداش مدیریت', 'amount' => $payrollValue('ماندگاری پاداش مدیریت')],
            ['label' => 'سایر مزایا', 'amount' => $payrollValue('ساير مزايا')],
            ['label' => 'رتبه‌بندی', 'amount' => $payrollValue('رتبه بندی')],
            ['label' => 'حق مأموریت', 'amount' => $payrollValue('حق ماموريت')],
            ['label' => 'پرداختی معوق', 'amount' => $payrollValue('پرداختی معوق')],
        ];

        $deductions = [
            ['label' => 'بیمه سهم کارمند', 'amount' => $payrollValue('بيمه سهم كارمند')],
            ['label' => 'مالیات ماه', 'amount' => $payrollValue('ماليات ماه')],
            ['label' => 'بیمه تکمیلی', 'amount' => $payrollValue('بيمه تكميلی')],
            ['label' => 'کسر جاری کارکنان', 'amount' => $payrollValue('كسر جاری كاركنان')],
            ['label' => 'وام ضروری', 'amount' => $payrollValue('وام ضروری')],
            ['label' => 'وام ضروری 3', 'amount' => $payrollValue('وام ضروری 3')],
            ['label' => 'خرید کارکنان 1', 'amount' => $payrollValue('خريد كاركنان 1')],
            ['label' => 'خرید کارکنان 2', 'amount' => $payrollValue('خريد كاركنان 2')],
            ['label' => 'خرید فروردین آریا', 'amount' => $payrollValue('خريد فروردين آريا')],
            ['label' => 'خرید اردیبهشت آریا', 'amount' => $payrollValue('خريد اريبهشت آريا')],
            ['label' => 'خرید خرداد آریا', 'amount' => $payrollValue('خريد خرداد آريا')],
            ['label' => 'خرید تیر آریا', 'amount' => $payrollValue('خرید تیر آریا')],
            ['label' => 'خرید مرداد آریا', 'amount' => $payrollValue('خريد مرداد آريا')],
            ['label' => 'خرید شهریور آریا', 'amount' => $payrollValue('خريد شهريور آريا')],
            ['label' => 'خرید مهر آریا', 'amount' => $payrollValue('خريد مهر آريا')],
            ['label' => 'خرید آبان آریا', 'amount' => $payrollValue('خريد آبان آريا')],
            ['label' => 'خرید آذر آریا', 'amount' => $payrollValue('خريد آذر آريا')],
            ['label' => 'خرید دی آریا', 'amount' => $payrollValue('خريد دی آريا')],
            ['label' => 'خرید بهمن آریا', 'amount' => $payrollValue('خريد بهمن آريا')],
            ['label' => 'خرید فروردین چادرملو', 'amount' => $payrollValue('خريد فروردين چادرملو')],
            ['label' => 'خرید اردیبهشت چادرملو', 'amount' => $payrollValue('خريد ارديبهشت چادرملو')],
            ['label' => 'خرید خرداد چادرملو', 'amount' => $payrollValue('خريد خرداد چادرملو')],
            ['label' => 'خرید تیر چادرملو', 'amount' => $payrollValue('خريد تير چادرملو')],
            ['label' => 'خرید مرداد چادرملو', 'amount' => $payrollValue('خريد مرداد چادرملو')],
            ['label' => 'خرید شهریور چادرملو', 'amount' => $payrollValue('خريد شهريور چادرملو')],
            ['label' => 'خرید مهر چادرملو', 'amount' => $payrollValue('خريد مهر چادرملو')],
            ['label' => 'خرید آبان چادرملو', 'amount' => $payrollValue('خريد آبان چادرملو')],
            ['label' => 'خرید آذر چادرملو', 'amount' => $payrollValue('خريد آذر چادرملو')],
            ['label' => 'خرید دی چادرملو', 'amount' => $payrollValue('خريد دي چادرملو')],
            ['label' => 'خرید بهمن چادرملو', 'amount' => $payrollValue('خريد بهمن چادرملو')],
            ['label' => 'فروردین ابوطالبی', 'amount' => $payrollValue('خريد فروردين ابوطالبی')],
            ['label' => 'اردیبهشت ابوطالبی', 'amount' => $payrollValue('خريد ارديبهشت ابوطالبی')],
            ['label' => 'خرداد ابوطالبی', 'amount' => $payrollValue('خريد خرداد ابوطالبی')],
            ['label' => 'تیر ابوطالبی', 'amount' => $payrollValue('خريد تير ابوطالبی')],
            ['label' => 'مرداد ابوطالبی', 'amount' => $payrollValue('خريد مرداد ابوطالبی')],
            ['label' => 'شهریور ابوطالبی', 'amount' => $payrollValue('خريد شهريور ابوطالبی')],
            ['label' => 'مهر ابوطالبی', 'amount' => $payrollValue('خريد مهر ابوطالبی')],
            ['label' => 'آبان ابوطالبی', 'amount' => $payrollValue('خريد آبان ابوطالبی')],
            ['label' => 'آذر ابوطالبی', 'amount' => $payrollValue('خريد آذر ابوطالبی')],
            ['label' => 'دی ابوطالبی', 'amount' => $payrollValue('خريد دي ابوطالبی')],
            ['label' => 'بهمن ابوطالبی', 'amount' => $payrollValue('خريد بهمن ابوطالبی')],
            ['label' => 'خرید فروردین شیشه', 'amount' => $payrollValue('خريد فروردين شیشه')],
            ['label' => 'خرید اردیبهشت شیشه', 'amount' => $payrollValue('خرید اردیبهشت شیشه')],
            ['label' => 'خرید خرداد شیشه', 'amount' => $payrollValue('خريد خرداد شیشه')],
            ['label' => 'خرید تیر شیشه', 'amount' => $payrollValue('خريد تير شیشه')],
            ['label' => 'خرید مرداد شیشه', 'amount' => $payrollValue('خريد مرداد شیشه')],
            ['label' => 'خرید شهریور شیشه', 'amount' => $payrollValue('خريد شهريور شیشه')],
            ['label' => 'خرید مهر شیشه', 'amount' => $payrollValue('خريد مهر شیشه')],
            ['label' => 'خرید آبان شیشه', 'amount' => $payrollValue('خريد آبان شیشه')],
            ['label' => 'خرید آذر شیشه', 'amount' => $payrollValue('خريد آذر شیشه')],
            ['label' => 'خرید دی شیشه', 'amount' => $payrollValue('خريد دي شیشه')],
            ['label' => 'خرید بهمن شیشه', 'amount' => $payrollValue('خريد بهمن شیشه')],
            ['label' => 'خرید فروردین سالار', 'amount' => $payrollValue('خرید فروردین سالار')],
            ['label' => 'خرید اردیبهشت سالار', 'amount' => $payrollValue('خرید اردیبهشت سالار')],
            ['label' => 'خرید خرداد سالار', 'amount' => $payrollValue('خريد خرداد سالار')],
            ['label' => 'خرید تیر سالار', 'amount' => $payrollValue('خريد تير سالار')],
            ['label' => 'خرید مرداد سالار', 'amount' => $payrollValue('خريد مرداد سالار')],
            ['label' => 'خرید شهریور سالار', 'amount' => $payrollValue('خريد شهريور سالار')],
            ['label' => 'خرید مهر سالار', 'amount' => $payrollValue('خريد مهر سالار')],
            ['label' => 'خرید آبان سالار', 'amount' => $payrollValue('خريد آبان سالار')],
            ['label' => 'خرید آذر سالار', 'amount' => $payrollValue('خريد آذر سالار')],
            ['label' => 'خرید دی سالار', 'amount' => $payrollValue('خريد دي سالار')],
            ['label' => 'خرید بهمن سالار', 'amount' => $payrollValue('خريد بهمن سالار')],
        ];

        $attendances = [
            ['label' => 'کارکرد عادی', 'value' => $payrollValue('دقیقه كاركرد عادی')],
            ['label' => 'بیماری', 'value' => $payrollValue('دقیقه بيماری')],
            ['label' => 'غیبت', 'value' => $payrollValue('دقیقه غيبت')],
            ['label' => 'مرخصی بدون حقوق', 'value' => $payrollValue('دقیقه مرخصی بدون حقوق')],
            ['label' => 'جمعه‌کاری', 'value' => $payrollValue('دقیقه جمعه كاری')],
        ];

        $totalPayments = $payrollValue('جمع ناخالص پرداختی');
        $totalDeductions = $payrollValue('جمع کسورات');
        $netPay = $payrollValue('خالص پرداختی');
        $bankAccount = $payrollValue('شماره حساب بانكی');
        $bankName = $payrollValue('نام بانک');

        $data = [
            'year' => $year,
            'monthName' => $monthName,
            'employeeInfo' => $employeeInfo,
            'payments' => $payments,
            'deductions' => $deductions,
            'attendances' => $attendances,
            'totalPayments' => $totalPayments,
            'totalDeductions' => $totalDeductions,
            'netPay' => $netPay,
            'bankAccount' => $bankAccount,
            'bankName' => $bankName,
        ];

        return Pdf::view('pdf.payroll-slip', $data)
            ->format('a5')
            ->landscape()
            ->name("فیش حقوقی {$monthName} {$year}.pdf");
    }
}
