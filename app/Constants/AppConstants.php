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
}
