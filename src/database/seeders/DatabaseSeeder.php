<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
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
        $users = User::factory()->count(10)->create();

        foreach ($users as $user) {
            Attendance::factory()
            ->count(60)
            ->state(['user_id' => $user->id])
            ->make()
            ->each(function ($attendance) use ($user) {
                $record = Attendance::updateOrCreate(
                    [
                        'user_id' => $user->id,
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
        $this->call([
            AdminUserSeeder::class,
        ]);
    }
}
