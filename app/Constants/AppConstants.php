<?php

namespace App\Constants;

class AppConstants
{
    public const MAX_FILE_SIZE = '5120';

    public const QUESTION_TYPES = [
        'RATING' => 1,
        'SINGLE_CHOICE' => 2,
        'MULTIPLE_CHOICE' => 3,
        'OPEN_ENDED' => 4,
    ];
    public const MAX_HOURLY_LEAVE_MINUTES = 210;
    public const HR_REQUEST_PENDING_STATUS = 1;
    public const HR_REQUEST_APPROVED_STATUS = 2;
    public const HR_REQUEST_REJECTED_STATUS = 3;

    public const HR_REQUEST_TYPES = [
        'DAILY_LEAVE' => 1,
        'HOURLY_LEAVE' => 2,
        'OVERTIME' => 3,
        'SICK' => 4
    ];

    public const WORK_DAY_MINUTES = 450;

    public const KASRA_REPORTS = [
      'ATTENDANCE_LOGS' => 238,
        'REMAINING_LEAVE' => 6,
    ];

    public const MAX_NEGATIVE_LEAVE_MINUTES = 1320;

}
