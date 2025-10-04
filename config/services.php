<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'rayvarz' => [
        'username' => env('RAYVARZ_USERNAME'),
        'password' => env('RAYVARZ_PASSWORD'),
        'client_id' => env('RAYVARZ_CLIENT_ID'),
        'client_secret' => env('RAYVARZ_CLIENT_SECRET'),
        'get_access_token' => env('RAYVARZ_GET_ACCESS_TOKEN'),
        'get_access_token_for_reports' => env('RAYVARZ_GET_ACCESS_TOKEN_FOR_REPORTS'),
        'fetch' => [
            'users' => env('RAYVARZ_FETCH_USERS'),
            'other_models' => env('RAYVARZ_FETCH_OTHER_MODELS'),
            'reports' => env('RAYVARZ_FETCH_REPORTS'),
        ],
    ],

    'kasra' => [
        'fetch' => [
            'users' => env('KASRA_FETCH_USERS'),
        ],
    ],

    'porsline' => [
        'authorization' => env('PORSLINE_AUTHORIZATION'),
        'create_new_variables' => env('PORSLINE_CREATE_NEW_VARIABLES'),
    ],

    'sms_pishgamrayan_token' => env('SMS_PISHGAMRAYAN_TOKEN'),

    'legacy_integrated_system' => [
        'get_tender_by_token' => env('LEGACY_INTEGRATED_SYSTEM_GET_TENDER_BY_TOKEN'),
        'get_active_tenders' => env('LEGACY_INTEGRATED_SYSTEM_GET_ACTIVE_TENDERS'),
        'submit_bid' => env('LEGACY_INTEGRATED_SYSTEM_SUBMIT_BID'),
        'get_completed_onboarding_courses' => env('LEGACY_INTEGRATED_SYSTEM_GET_COMPLETED_ONBOARDING_COURSES'),
        'get_reassignment_data' => env('LEGACY_INTEGRATED_SYSTEM_GET_REASSIGNMENT_DATA'),
    ],

    'productivity_system' => [
        'get_reward_and_fines_data' => env('PRODUCTIVITY_SYSTEM_GET_REWARD_AND_FINES_DATA'),
    ],

    'payroll_system' => [
        'get_assessment_data' => env('PAYROLL_SYSTEM_GET_ASSESSMENT_DATA'),
        'get_payroll_data' => env('PAYROLL_SYSTEM_GET_SALARY_DATA'),
        'get_birthday_gift_data' => env('PAYROLL_SYSTEM_GET_BIRTHDAY_GIFT_DATA'),
    ],

    'food_reservation_system' => [
        'get_food_reservation_data' => env('FOOD_RESERVATION_SYSTEM_GET_RESERVE_DATA'),
    ],
];
