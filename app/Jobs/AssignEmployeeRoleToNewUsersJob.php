<?php

namespace App\Jobs;

use App\Models\User;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

class AssignEmployeeRoleToNewUsersJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $users = User::active()->get();

        foreach ($users as $user) {
            if ($user->roles->count() === 0) {
                $user->assignRole('employee');
            }
        }
    }
}
