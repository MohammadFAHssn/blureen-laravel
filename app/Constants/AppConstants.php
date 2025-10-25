<?php

namespace App\Constants;

class AppConstants
{
    public const MAX_FILE_SIZE = '5120';

    public const MAX_HOURLY_LEAVE_MINUTES = 210;
    public const HR_REQUEST_PENDING_STATUS = 1;
    public const HR_REQUEST_APPROVED_STATUS = 2;
    public const HR_REQUEST_REJECTED_STATUS = 3;
    public const HR_REQUEST_TYPE_DAILY_LEAVE = 1;
    public const HR_REQUEST_TYPE_HOURLY_LEAVE = 2;
    public const HR_REQUEST_TYPE_OVERTIME = 3;
    public const HR_REQUEST_TYPE_SICK = 4;

}
