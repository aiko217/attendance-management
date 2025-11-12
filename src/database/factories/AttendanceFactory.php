<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Attendance;
use Carbon\Carbon;

class AttendanceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    protected $model = Attendance::class;

    public function definition()
    {
        $startDate = Carbon::create(2025, 10, 1);
        $endDate =  Carbon::create(2025, 12, 31);

        do {
            $date = Carbon::instance($this->faker->dateTimeBetween($startDate, $endDate));
        } while ($date->isWeekend());

        $clockIn = $date->copy()->setTime(9, 0, 0);
        $clockOut = $date->copy()->setTime(18, 0, 0);

        $breakMinutes = 60;
        $workMinutes = $clockIn->diffInMinutes($clockOut) - $breakMinutes;
       
        return [
            'user_id' => $this->faker->numberBetween(1, 3),
            'date' => $date->toDateString(),
            'clock_in' =>$clockIn->toTimeString(),
            'clock_out' =>$clockOut->toTimeString(),
            'total_break_time' => '01:00:00',
            'work_time' => sprintf('%02d:%02d:00', floor($workMinutes / 60), $workMinutes % 60),
            'attendance_status' => '退勤済',
        ];
    }
    public function withBreak()
    {
        return $this->afterCreating(function (Attendance $attendance) {
            $attendance->breaks()->create([
                'break_start'=> '12:00:00',
                'break_end'=> '13:00:00',
            ]);
        });
    }
}
