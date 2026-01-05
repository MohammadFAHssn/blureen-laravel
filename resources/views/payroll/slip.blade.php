@php
    $shabnam = base64_encode(file_get_contents(public_path('fonts/Shabnam-Light-FD.ttf')));
@endphp

<!DOCTYPE html>
<html lang="fa" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>فیش حقوقی - {{ $monthName }} {{ $year }}</title>
    <style>
        @font-face {
            font-family: 'Shabnam';
            src: url("data:font/truetype;charset=utf-8;base64,{{ $shabnam }}") format('truetype');
            font-weight: normal;
            font-style: normal;
        }

        @page {
            size: A5 landscape;
            margin: 8mm;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Shabnam', 'Tahoma', sans-serif;
            font-size: 9px;
            line-height: 1.4;
            color: #2c3e50;
            background: #fff;
            direction: rtl;
        }

        .container {
            width: 100%;
            max-width: 190mm;
            height: 128mm;
            margin: 0 auto;
            padding: 0;
            display: flex;
            flex-direction: column;
        }

        /* Header - Modern Outlined Style */
        .header {
            background: #f0f7fc;
            border: 2px solid #1a5276;
            border-radius: 8px;
            padding: 6px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }

        .header-title {
            font-size: 12px;
            font-weight: bold;
            color: #1a5276;
            letter-spacing: 0.5px;
        }

        .header-date {
            font-size: 10px;
            color: #1a5276;
            border: 2px solid #1a5276;
            padding: 5px 14px;
            border-radius: 20px;
            font-weight: bold;
            background: #fff;
        }

        /* Main Content - Three Column Layout */
        .main-content {
            display: flex;
            gap: 8px;
            flex: 1;
        }

        /* Right Column - Employee Info & Attendance */
        .right-column {
            width: 180px;
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .employee-info {
            border: 1.5px solid #3498db;
            border-radius: 5px;
            padding: 8px;
            background: transparent;
        }

        .section-title {
            background: transparent;
            color: #1a5276;
            border: 1.5px solid #1a5276;
            padding: 4px 10px;
            border-radius: 4px;
            font-size: 10px;
            font-weight: bold;
            margin-bottom: 6px;
            text-align: center;
            display: inline-block;
            width: 100%;
        }

        .info-grid {
            display: flex;
            flex-direction: column;
            gap: 3px;
        }

        .info-item {
            display: flex;
            justify-content: space-between;
            padding: 3px 6px;
            border: 1px solid #95a5a6;
            border-radius: 3px;
            font-size: 8px;
            background: transparent;
        }

        .info-label {
            color: #566573;
            font-weight: normal;
        }

        .info-value {
            color: #2c3e50;
            font-weight: bold;
        }

        /* Attendance Section */
        .attendance-info {
            border: 1.5px solid #9b59b6;
            border-radius: 5px;
            padding: 8px;
            background: transparent;
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        .attendance-info .section-title {
            border-color: #9b59b6;
            color: #9b59b6;
        }

        .attendance-item {
            display: flex;
            justify-content: space-between;
            padding: 3px 6px;
            border: 1px solid #a569bd;
            border-radius: 3px;
            margin-bottom: 3px;
            font-size: 8px;
            background: transparent;
        }

        /* Middle & Left Columns - Tables */
        .table-column {
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        .table-container {
            flex: 1;
            border: 1.5px solid #3498db;
            border-radius: 5px;
            overflow: hidden;
            background: transparent;
            display: flex;
            flex-direction: column;
        }

        .table-container.deductions {
            border-color: #e74c3c;
            position: relative;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 8px;
        }

        .data-table thead tr:first-child th {
            background: transparent;
            color: #1a5276;
            border-bottom: 1.5px solid #3498db;
            padding: 5px;
            text-align: center;
            font-weight: bold;
            font-size: 9px;
        }

        .table-container.deductions .data-table thead tr:first-child th {
            color: #c0392b;
            border-bottom-color: #e74c3c;
        }

        .data-table thead tr:last-child th {
            background: transparent;
            color: #566573;
            padding: 4px;
            text-align: center;
            font-weight: normal;
            font-size: 7px;
            border-bottom: 1px solid #aab7b8;
        }

        .data-table td {
            padding: 6px 8px;
            border-bottom: 1px solid #d5d8dc;
        }

        .data-table tbody tr:nth-child(odd) {
            background: #f8f9f9;
        }

        .row-number {
            width: 22px;
            text-align: center;
            color: #7f8c8d;
            font-size: 8px;
        }

        .amount-cell {
            text-align: left;
            font-family: 'Shabnam', 'Tahoma', sans-serif;
            direction: ltr;
            font-weight: bold;
            color: #27ae60;
            font-size: 9px;
        }

        .table-container.deductions .amount-cell {
            color: #e74c3c;
        }

        .label-cell {
            text-align: right;
            color: #2c3e50;
        }

        /* Bottom Section - Full Width */
        .bottom-section {
            display: flex;
            gap: 8px;
            align-items: center;
            margin-top: 6px;
        }

        /* Summary Section */
        .summary-section {
            display: flex;
            gap: 6px;
            flex: 1;
        }

        .summary-box {
            flex: 1;
            background: transparent;
            border: 2px solid #95a5a6;
            border-radius: 6px;
            padding: 10px 8px;
            text-align: center;
            display: flex;
            flex-direction: column;
            justify-content: center;
            height: 50px;
        }

        .summary-box.payments {
            border-color: #27ae60;
            background: #eafaf1;
        }

        .summary-box.deductions {
            border-color: #e74c3c;
            background: #fdedec;
        }

        .summary-box.net-pay {
            border: 2.5px solid #1a5276;
            background: #eaf2f8;
        }

        .summary-label {
            font-size: 9px;
            color: #566573;
            margin-bottom: 4px;
            font-weight: bold;
        }

        .summary-box.net-pay .summary-label {
            color: #1a5276;
            font-weight: bold;
        }

        .summary-value {
            font-size: 13px;
            font-weight: bold;
            font-family: 'Shabnam', 'Tahoma', sans-serif;
            direction: ltr;
        }

        .summary-box.payments .summary-value {
            color: #27ae60;
        }

        .summary-box.deductions .summary-value {
            color: #e74c3c;
        }

        .summary-box.net-pay .summary-value {
            color: #1a5276;
            font-size: 15px;
        }

        /* Footer Section */
        .footer-section {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .bank-info {
            background: transparent;
            border: 1.5px solid #f39c12;
            border-radius: 5px;
            padding: 8px 12px;
            flex: 1;
        }

        .bank-info-text {
            font-size: 9px;
            color: #b7950b;
        }

        .bank-account {
            font-weight: bold;
            font-family: 'Shabnam', 'Tahoma', sans-serif;
            direction: ltr;
            display: inline-block;
            color: #9a7d0a;
        }

        .stamp-section {
            position: absolute;
            bottom: 5px;
            left: 50%;
            transform: translateX(-50%);
            text-align: center;
            z-index: 10;
            pointer-events: none;
        }

        .stamp-image {
            width: 140px;
            height: auto;
            opacity: 0.85;
            border-radius: 5px;
        }

        /* Print Styles */
        @media print {
            body {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .container {
                padding: 0;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <div class="header-title">شیشه شناور اردکان</div>
            <div class="header-date">{{ $monthName }} {{ $year }}</div>
        </div>

        <!-- Main Content - Three Columns -->
        <div class="main-content">
            <!-- Right Column - Employee Info & Attendance -->
            <div class="right-column">
                <div class="employee-info">
                    <div class="section-title">اطلاعات پرسنلی</div>
                    <div class="info-grid">
                        @foreach($employeeInfo as $info)
                            @if($info['value'])
                                <div class="info-item">
                                    <span class="info-label">{{ $info['label'] }}:</span>
                                    <span class="info-value">{{ $info['value'] }}</span>
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>

                <div class="attendance-info">
                    <div class="section-title">کارکرد و غیبت</div>
                    @foreach($attendances as $attendance)
                        @if($attendance['value'])
                            <div class="attendance-item">
                                <span class="info-label">{{ $attendance['label'] }}:</span>
                                <span class="info-value">{{ number_format($attendance['value']) }} دقیقه</span>
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>

            <!-- Middle Column - Payments -->
            <div class="table-column">
                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th colspan="3">پرداخت‌ها</th>
                            </tr>
                            <tr>
                                <th>#</th>
                                <th>عنوان</th>
                                <th>ریال</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $paymentIndex = 0; @endphp
                            @foreach($payments as $payment)
                                @if($payment['amount'])
                                    @php $paymentIndex++; @endphp
                                    <tr>
                                        <td class="row-number">{{ $paymentIndex }}</td>
                                        <td class="label-cell">{{ $payment['label'] }}</td>
                                        <td class="amount-cell">{{ number_format($payment['amount']) }}</td>
                                    </tr>
                                @endif
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Left Column - Deductions -->
            <div class="table-column">
                <div class="table-container deductions">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th colspan="3">کسورات</th>
                            </tr>
                            <tr>
                                <th>#</th>
                                <th>عنوان</th>
                                <th>ریال</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $deductionIndex = 0; @endphp
                            @foreach($deductions as $deduction)
                                @if($deduction['amount'])
                                    @php $deductionIndex++; @endphp
                                    <tr>
                                        <td class="row-number">{{ $deductionIndex }}</td>
                                        <td class="label-cell">{{ $deduction['label'] }}</td>
                                        <td class="amount-cell">{{ number_format($deduction['amount']) }}</td>
                                    </tr>
                                @endif
                            @endforeach
                        </tbody>
                    </table>
                    <div class="stamp-section">
                        <img src="{{ public_path('stamp.png') }}" alt="مهر شرکت" class="stamp-image">
                    </div>
                </div>
            </div>
        </div>

            <!-- Bottom Section - Summary & Footer -->
            <div class="bottom-section">
                <!-- Summary Section -->
                <div class="summary-section">
                    <div class="summary-box payments">
                        <div class="summary-label">جمع پرداخت‌ها</div>
                        <div class="summary-value">{{ number_format($totalPayments) }}</div>
                    </div>
                    <div class="summary-box deductions">
                        <div class="summary-label">جمع کسورات</div>
                        <div class="summary-value">-{{ number_format($totalDeductions) }}</div>
                    </div>
                    <div class="summary-box net-pay">
                        <div class="summary-label">خالص پرداختی</div>
                        <div class="summary-value">{{ number_format($netPay) }}</div>
                    </div>
                </div>

                <!-- Footer -->
                <div class="footer-section">
                    <div class="bank-info">
                        <div class="bank-info-text">
                            مبلغ <strong>{{ number_format($netPay) }}</strong> ریال نزد بانک <span class="bank-account">{{ $bankName }}</span> به شماره حساب
                            <span class="bank-account">{{ $bankAccount }}</span> واریز شد.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>
