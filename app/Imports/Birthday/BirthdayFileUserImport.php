<?php

namespace App\Imports\Birthday;

use App\Models\Birthday\BirthdayFileUser;
use App\Models\User;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Imports\HeadingRowFormatter;
use Illuminate\Support\Facades\Auth;

HeadingRowFormatter::default('none');

use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;

class BirthdayFileUserImport implements ToModel, WithChunkReading, WithBatchInserts, WithHeadingRow, SkipsOnFailure
{
    use Importable, SkipsFailures;

    public $birthdayFileId;

    public function __construct($birthdayFileId)
    {
        $this->birthdayFileId = $birthdayFileId;
    }

    public function model(array $row)
    {
        $user = User::where('personnel_code', $row['شماره پرسنلي'])->first();

        if (!$user) {
            return null;
        }

        return new BirthdayFileUser([
            'birthday_file_id' => $this->birthdayFileId,
            'user_id' => $user->id,
            'created_by' => Auth::id(),
        ]);
    }

    public function batchSize(): int
    {
        return 300;
    }

    public function chunkSize(): int
    {
        return 300;
    }
}
