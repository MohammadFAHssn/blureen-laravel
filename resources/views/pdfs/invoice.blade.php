<!doctype html>
<html lang="fa" dir="rtl">

<head>
    <meta charset="utf-8">
    <style>
        @font-face {
            font-family: 'Shabnam';
            src: url('{{ public_path('fonts/Shabnam-Light-FD.ttf') }}') format('truetype');
            font-weight: normal;
            font-style: normal;
        }

        html {
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        /* رنگ پس‌زمینه در PDF */
        body {
            font-family: 'Shabnam', sans-serif;
            direction: rtl;
        }

        h1 {
            margin-bottom: 8px;
        }

        .box {
            border: 1px solid #ddd;
            padding: 16px;
            border-radius: 8px;
        }
    </style>
</head>

<body>
    <h1>فاکتور شماره {{ $invoice['id'] }}</h1>
    <div class="box">
        <p>مشتری: {{ $invoice['customer'] }}</p>
        <p>مبلغ: {{ number_format($invoice['amount']) }} تومان</p>
    </div>

    {{-- نمونه شکست صفحه --}}
    @pageBreak

    <p>این صفحهٔ دوم است.</p>
</body>

</html>
