<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | خطوط زبانی زیر شامل پیغامهای پیشفرض خطاهای اعتبارسنجی می‌باشد.
    | برخی از این قوانین نسخه‌های متعددی دارند مانند قوانین اندازه.
    | میتوانید هر یک از این پیامها را در اینجا شخصی‌سازی کنید.
    |
    */

    'accepted' => 'فیلد :attribute باید پذیرفته شده باشد.',
    'accepted_if' => 'فیلد :attribute باید پذیرفته شده باشد وقتی :other برابر :value است.',
    'active_url' => 'فیلد :attribute باید یک آدرس معتبر اینترنتی باشد.',
    'after' => 'فیلد :attribute باید تاریخی بعد از :date باشد.',
    'after_or_equal' => 'فیلد :attribute باید تاریخی برابر یا بعد از :date باشد.',
    'alpha' => 'فیلد :attribute باید فقط شامل حروف باشد.',
    'alpha_dash' => 'فیلد :attribute باید فقط شامل حروف، اعداد، خط تیره و زیرخط باشد.',
    'alpha_num' => 'فیلد :attribute باید فقط شامل حروف و اعداد باشد.',
    'any_of' => 'مقدار فیلد :attribute نامعتبر است.',
    'array' => 'فیلد :attribute باید یک آرایه باشد.',
    'ascii' => 'فیلد :attribute باید فقط شامل نویسه‌های الفبایی عددی تک‌بایتی و نمادها باشد.',
    'before' => 'فیلد :attribute باید تاریخی قبل از :date باشد.',
    'before_or_equal' => 'فیلد :attribute باید تاریخی برابر یا قبل از :date باشد.',
    'between' => [
        'array' => 'فیلد :attribute باید بین :min تا :max آیتم داشته باشد.',
        'file' => 'فیلد :attribute باید بین :min تا :max کیلوبایت باشد.',
        'numeric' => 'فیلد :attribute باید بین :min تا :max باشد.',
        'string' => 'فیلد :attribute باید بین :min تا :max کاراکتر باشد.',
    ],
    'boolean' => 'فیلد :attribute باید صحیح یا غلط باشد.',
    'can' => 'فیلد :attribute حاوی یک مقدار غیرمجاز است.',
    'confirmed' => 'تأییدیه فیلد :attribute مطابقت ندارد.',
    'contains' => 'فیلد :attribute فاقد مقدار مورد نیاز است.',
    'current_password' => 'رمزعبور وارد شده نادرست است.',
    'date' => 'فیلد :attribute باید یک تاریخ معتبر باشد.',
    'date_equals' => 'فیلد :attribute باید تاریخی برابر با :date باشد.',
    'date_format' => 'فیلد :attribute باید با فرمت :format مطابقت داشته باشد.',
    'decimal' => 'فیلد :attribute باید :decimal رقم اعشار داشته باشد.',
    'declined' => 'فیلد :attribute باید رد شده باشد.',
    'declined_if' => 'فیلد :attribute باید رد شده باشد وقتی :other برابر :value است.',
    'different' => 'فیلد :attribute و :other باید متفاوت باشند.',
    'digits' => 'فیلد :attribute باید :digits رقم باشد.',
    'digits_between' => 'فیلد :attribute باید بین :min تا :max رقم باشد.',
    'dimensions' => 'فیلد :attribute دارای ابعاد تصویر نامعتبر است.',
    'distinct' => 'فیلد :attribute مقدار تکراری دارد.',
    'doesnt_end_with' => 'فیلد :attribute نباید با یکی از مقادیر زیر پایان یابد: :values.',
    'doesnt_start_with' => 'فیلد :attribute نباید با یکی از مقادیر زیر شروع شود: :values.',
    'email' => 'فیلد :attribute باید یک آدرس ایمیل معتبر باشد.',
    'ends_with' => 'فیلد :attribute باید با یکی از مقادیر زیر پایان یابد: :values.',
    'enum' => 'مقدار انتخاب شده برای :attribute نامعتبر است.',
    'exists' => ':attribute موجود نمی‌باشد.',
    'extensions' => 'فیلد :attribute باید دارای یکی از پسوندهای زیر باشد: :values.',
    'file' => 'فیلد :attribute باید یک فایل باشد.',
    'filled' => 'فیلد :attribute باید دارای مقدار باشد.',
    'gt' => [
        'array' => 'فیلد :attribute باید بیشتر از :value آیتم داشته باشد.',
        'file' => 'فیلد :attribute باید بزرگتر از :value کیلوبایت باشد.',
        'numeric' => 'فیلد :attribute باید بزرگتر از :value باشد.',
        'string' => 'فیلد :attribute باید بیش از :value کاراکتر داشته باشد.',
    ],
    'gte' => [
        'array' => 'فیلد :attribute باید :value آیتم یا بیشتر داشته باشد.',
        'file' => 'فیلد :attribute باید برابر یا بزرگتر از :value کیلوبایت باشد.',
        'numeric' => 'فیلد :attribute باید برابر یا بزرگتر از :value باشد.',
        'string' => 'فیلد :attribute باید حداقل :value کاراکتر داشته باشد.',
    ],
    'hex_color' => 'فیلد :attribute باید یک رنگ HEX معتبر باشد.',
    'image' => 'فیلد :attribute باید یک تصویر باشد.',
    'in' => 'مقدار انتخاب شده برای :attribute نامعتبر است.',
    'in_array' => 'فیلد :attribute باید در :other موجود باشد.',
    'integer' => 'فیلد :attribute باید یک عدد صحیح باشد.',
    'ip' => 'فیلد :attribute باید یک آدرس IP معتبر باشد.',
    'ipv4' => 'فیلد :attribute باید یک آدرس IPv4 معتبر باشد.',
    'ipv6' => 'فیلد :attribute باید یک آدرس IPv6 معتبر باشد.',
    'json' => 'فیلد :attribute باید یک رشته JSON معتبر باشد.',
    'list' => 'فیلد :attribute باید یک لیست باشد.',
    'lowercase' => 'فیلد :attribute باید با حروف کوچک باشد.',
    'lt' => [
        'array' => 'فیلد :attribute باید کمتر از :value آیتم داشته باشد.',
        'file' => 'فیلد :attribute باید کوچکتر از :value کیلوبایت باشد.',
        'numeric' => 'فیلد :attribute باید کوچکتر از :value باشد.',
        'string' => 'فیلد :attribute باید کمتر از :value کاراکتر داشته باشد.',
    ],
    'lte' => [
        'array' => 'فیلد :attribute نباید بیشتر از :value آیتم داشته باشد.',
        'file' => 'فیلد :attribute باید برابر یا کوچکتر از :value کیلوبایت باشد.',
        'numeric' => 'فیلد :attribute باید برابر یا کوچکتر از :value باشد.',
        'string' => 'فیلد :attribute باید حداکثر :value کاراکتر داشته باشد.',
    ],
    'mac_address' => 'فیلد :attribute باید یک آدرس MAC معتبر باشد.',
    'max' => [
        'array' => 'فیلد :attribute نباید بیشتر از :max آیتم داشته باشد.',
        'file' => 'فیلد :attribute نباید بزرگتر از :max کیلوبایت باشد.',
        'numeric' => 'فیلد :attribute نباید بزرگتر از :max باشد.',
        'string' => 'فیلد :attribute نباید بیش از :max کاراکتر داشته باشد.',
    ],
    'max_digits' => 'فیلد :attribute نباید بیشتر از :max رقم داشته باشد.',
    'mimes' => 'فیلد :attribute باید از نوع فایل: :values باشد.',
    'mimetypes' => 'فیلد :attribute باید از نوع فایل: :values باشد.',
    'min' => [
        'array' => 'فیلد :attribute باید حداقل :min آیتم داشته باشد.',
        'file' => 'فیلد :attribute باید حداقل :min کیلوبایت باشد.',
        'numeric' => 'فیلد :attribute باید حداقل :min باشد.',
        'string' => 'فیلد :attribute باید حداقل :min کاراکتر داشته باشد.',
    ],
    'min_digits' => 'فیلد :attribute باید حداقل :min رقم داشته باشد.',
    'missing' => 'فیلد :attribute نباید موجود باشد.',
    'missing_if' => 'فیلد :attribute نباید موجود باشد وقتی :other برابر :value است.',
    'missing_unless' => 'فیلد :attribute نباید موجود باشد مگر اینکه :other برابر :value باشد.',
    'missing_with' => 'فیلد :attribute نباید موجود باشد وقتی :values موجود است.',
    'missing_with_all' => 'فیلد :attribute نباید موجود باشد وقتی :values موجود هستند.',
    'multiple_of' => 'فیلد :attribute باید مضربی از :value باشد.',
    'not_in' => 'مقدار انتخاب شده برای :attribute نامعتبر است.',
    'not_regex' => 'فرمت فیلد :attribute نامعتبر است.',
    'numeric' => 'فیلد :attribute باید یک عدد باشد.',
    'password' => [
        'letters' => 'فیلد :attribute باید حداقل شامل یک حرف باشد.',
        'mixed' => 'فیلد :attribute باید حداقل شامل یک حرف بزرگ و یک حرف کوچک باشد.',
        'numbers' => 'فیلد :attribute باید حداقل شامل یک عدد باشد.',
        'symbols' => 'فیلد :attribute باید حداقل شامل یک نماد باشد.',
        'uncompromised' => 'رمز وارد شده در نشت اطلاعاتی وجود دارد. لطفاً یک رمز دیگر انتخاب کنید.',
    ],
    'present' => 'فیلد :attribute باید موجود باشد.',
    'present_if' => 'فیلد :attribute باید موجود باشد وقتی :other برابر :value است.',
    'present_unless' => 'فیلد :attribute باید موجود باشد مگر اینکه :other برابر :value باشد.',
    'present_with' => 'فیلد :attribute باید موجود باشد وقتی :values موجود است.',
    'present_with_all' => 'فیلد :attribute باید موجود باشد وقتی :values موجود هستند.',
    'prohibited' => 'فیلد :attribute ممنوع است.',
    'prohibited_if' => 'فیلد :attribute ممنوع است وقتی :other برابر :value است.',
    'prohibited_if_accepted' => 'فیلد :attribute ممنوع است وقتی :other پذیرفته شده است.',
    'prohibited_if_declined' => 'فیلد :attribute ممنوع است وقتی :other رد شده است.',
    'prohibited_unless' => 'فیلد :attribute ممنوع است مگر اینکه :other در :values موجود باشد.',
    'prohibits' => 'فیلد :attribute باعث ممنوعیت حضور :other می‌شود.',
    'regex' => 'فرمت فیلد :attribute نامعتبر است.',
    'required' => 'فیلد :attribute الزامی است.',
    'required_array_keys' => 'فیلد :attribute باید شامل ورودی‌هایی برای: :values باشد.',
    'required_if' => 'فیلد :attribute الزامی است وقتی :other برابر :value است.',
    'required_if_accepted' => 'فیلد :attribute الزامی است وقتی :other پذیرفته شده است.',
    'required_if_declined' => 'فیلد :attribute الزامی است وقتی :other رد شده است.',
    'required_unless' => 'فیلد :attribute الزامی است مگر اینکه :other در :values موجود باشد.',
    'required_with' => 'فیلد :attribute الزامی است وقتی :values موجود است.',
    'required_with_all' => 'فیلد :attribute الزامی است وقتی :values موجود هستند.',
    'required_without' => 'فیلد :attribute الزامی است وقتی :values موجود نیست.',
    'required_without_all' => 'فیلد :attribute الزامی است وقتی هیچکدام از :values موجود نیستند.',
    'same' => 'فیلد :attribute باید با :other مطابقت داشته باشد.',
    'size' => [
        'array' => 'فیلد :attribute باید شامل :size آیتم باشد.',
        'file' => 'فیلد :attribute باید :size کیلوبایت باشد.',
        'numeric' => 'فیلد :attribute باید برابر :size باشد.',
        'string' => 'فیلد :attribute باید :size کاراکتر داشته باشد.',
    ],
    'starts_with' => 'فیلد :attribute باید با یکی از مقادیر زیر شروع شود: :values.',
    'string' => 'فیلد :attribute باید یک رشته باشد.',
    'timezone' => 'فیلد :attribute باید یک منطقه زمانی معتبر باشد.',
    'unique' => 'این :attribute قبلا ثبت شده است.',
    'uploaded' => 'آپلود فیلد :attribute ناموفق بود.',
    'uppercase' => 'فیلد :attribute باید با حروف بزرگ باشد.',
    'url' => 'فیلد :attribute باید یک آدرس اینترنتی معتبر باشد.',
    'ulid' => 'فیلد :attribute باید یک ULID معتبر باشد.',
    'uuid' => 'فیلد :attribute باید یک UUID معتبر باشد.',

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    */

    'custom' => [
        'attribute-name' => [
            'rule-name' => 'پیام سفارشی',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    */

    'attributes' => [
        // مثال:
        'email' => 'آدرس ایمیل',
        'password' => 'رمز عبور',
        'username' => 'نام کاربری',
        'phoneNumber' => 'شماره تلفن همراه'
    ],

];
