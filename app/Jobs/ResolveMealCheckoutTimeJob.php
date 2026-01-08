<?php

namespace App\Jobs;

use App\Services\Api\KasraService;
use App\Services\Food\Rep\MealReservationContradictionService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class ResolveMealCheckoutTimeJob implements ShouldQueue
{
    use Queueable;

    protected $mealReservationContradictionService;
    protected $kasraService;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        $this->mealReservationContradictionService = new MealReservationContradictionService();
        $this->kasraService = new KasraService();
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        info('ResolveMealCheckoutTimeJob: started');
        $details = $this->mealReservationContradictionService->reservationDetailsNeedingCheckoutCheck();

        if ($details->isEmpty()) {
            info('ResolveMealCheckoutTimeJob: no details to check');
            return;
        }

        $now = now();

        foreach ($details as $detail) {
            $date = $this->jalaliDate($detail->reservation->date);

            $payload = [
                'user_id' => $detail->personnel->id,
                'start_date' => $date,
                'end_date' => $date,
            ];

            $report = $this->kasraService->getEmployeeAttendanceReport($payload);
            $attendances = $report['attendances'] ?? [];

            if (empty($attendances)) {
                info('ResolveMealCheckoutTimeJob: no attendances (not entered)', [
                    'meal_reservation_detail_id' => $detail->id,
                    'user_id' => $payload['user_id'],
                    'date' => $date,
                ]);
                $detail->update(['last_check_at' => $now]);
                continue;
            }

            $ins = [];
            $outs = [];

            foreach ($attendances as $attendance) {
                if (!empty($attendance['in']))
                    $ins[] = $attendance['in'];
                if (!empty($attendance['out']))
                    $outs[] = $attendance['out'];
            }

            if (empty($outs) || count($ins) > count($outs)) {
                info('ResolveMealCheckoutTimeJob: not checked out yet', [
                    'meal_reservation_detail_id' => $detail->id,
                    'user_id' => $payload['user_id'],
                    'date' => $date,
                ]);
                $detail->update(['last_check_at' => $now]);
                continue;
            }

            $latest = collect($attendances)
                ->filter(fn($a) => !empty($a['in']) && !empty($a['out']))
                ->sortByDesc('in')
                ->first();

            if (!$latest) {
                $detail->update(['last_check_at' => $now]);
                continue;
            }

            if ($latest['in'] > $latest['out']) {  // it is like this: in = 22:00, out = 01:00
                $detail->update([
                    'check_out_time' => $latest['out'],
                    'last_check_at' => $now,
                    'is_entitled' => true,
                ]);
                return;
            }

            $detail->update([
                'check_out_time' => $latest['out'],
                'last_check_at' => $now,
            ]);
        }
        info('ResolveMealCheckoutTimeJob: ended');
    }

    private function jalaliDate($date): string
    {
        return is_string($date) ? $date : \Carbon\Carbon::parse($date)->format('Y/m/d');
    }
}
