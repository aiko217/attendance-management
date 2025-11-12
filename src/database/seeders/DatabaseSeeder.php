<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Attendance;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        foreach ([1, 2, 3] as $userId) {
            Attendance::factory()
            ->count(60)
            ->state(['user_id' => $userId])
            ->make()
            ->each(function ($attendance) use ($userId) {
                $record = Attendance::updateOrCreate(
                    [
                        'user_id' => $userId,
                        'date' => $attendance->date,
                    ],
                    [
                        'clock_in' => $attendance->clock_in,
                        'clock_out' => $attendance->clock_out,
                        'total_break_time' => $attendance->total_break_time,
                        'work_time' => $attendance->work_time,
                        'attendance_status' => $attendance->attendance_status,  
                    ]
                );

                $record->breaks()->updateOrCreate(
                    ['attendance_id' => $record->id],
                    ['break_start' => '12:00:00', 'break_end' => '13:00:00']
                );
            });
        }
    }
}
