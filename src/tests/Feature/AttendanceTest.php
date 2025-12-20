<?php

namespace Tests\Feature;


use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakTime;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;

class AttendanceTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_clock_in()
    {
        Carbon::setTestNow('2025-01-01 09:00:00');

        $user = User::factory()->create();

        $this->actingAs($user)
        ->post('/attendance/clock-in');

        $this->assertDatabaseHas('attendances', [
            'user_id' => $user->id,
            'date' => '2025-01-01',
            'attendance_status' => '出勤中',
        ]);
    }

    public function test_user_can_clock_in_only_once_per_day() {
        Carbon::setTestNow('2025-01-01 09:00:00');

        $user = User::factory()->create();

        $this->actingAs($user)->post('/attendance/clock-in');
        $this->actingAs($user)->post('/attendance/clock-in');

        $this->assertEquals(
            1,
            Attendance::where('user_id', $user->id)
            ->where('date', '2025-01-01')
            ->count()
        );
    }

    public function test_clock_in_time_is_displayed_on_list()
    {
        Carbon::setTestNow('2025-01-01 09:00:00');

        $user = User::factory()->create();

        Attendance::create([
            'user_id' => $user->id,
            'date' => '2025-01-01',
            'clock_in' => '09:00:00',
            'attendance_status' => '出勤中',
        ]);

        $response = $this->actingAs($user)
            ->get('/attendance/list?year=2025&month=1');

        $response->assertSee('09:00');
    }

    public function test_user_can_start_break()
    {
        Carbon::setTestNow('2025-01-01 12:00:00');

        $user = User::factory()->create();

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => '2025-01-01',
            'clock_in' => '09:00:00',
            'attendance_status' => '出勤中',
        ]);

        $this->actingAs($user)
        ->post('/attendance/break-start');

        $this->assertDatabaseHas('breaks', [
            'attendance_id' => $attendance->id,
            'break_start' => '12:00:00',
        ]);

        $this->assertDatabaseHas('attendances', [
            'id' => $attendance->id,
            'attendance_status' 
            => '休憩中',
        ]);
    }

    public function test_user_can_take_multiple_breaks()
    {
        $user = User::factory()->create();

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => today(),
            'clock_in' => '09:00:00',
            'attendance_status' => '出勤中',
        ]);

        Carbon::setTestNow('12:00:00');
        $this->actingAs($user)->post('/attendance/break-start');
        $this->actingAs($user)->post('/attendance/break-end');

        Carbon::setTestNow('15:00:00');
        $this->actingAs($user)->post('/attendance/break-start');
        $this->actingAs($user)->post('/attendance/break-end');

        $this->assertEquals(2, BreakTime::count());
    }

    public function test_user_can_clock_out()
    {
        Carbon::setTestNow('2025-01-01 18:00:00');

        $user = User::factory()->create();

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => '2025-01-01',
            'clock_in' => '09:00:00',
            'attendance_status' => '出勤中',
        ]);

        $this->actingAs($user)
            ->post('/attendance/clock-out');

        $this->assertDatabaseHas('attendances', [
            'id' => $attendance->id,
            'attendance_status' => '退勤済',
            'clock_out' => '18:00:00',
        ]);
    }

    public function test_clock_out_time_and_work_time_are_displayed_on_list()
    {
        Carbon::setTestNow('2025-01-01 09:00:00');

        $user = User::factory()->create();
        $this->actingAs($user);

        $this->post('/attendance/clock-in');

        Carbon::setTestNow('2025-01-01 12:00:00');
        $this->post('/attendance/break-start');

        Carbon::setTestNow('2025-01-01 13:00:00');
        $this->post('/attendance/break-end');

        Carbon::setTestNow('2025-01-01 18:00:00');
        $this->post('/attendance/clock-out');

        $response = $this->get('/attendance/list?year=2025&month=1');

        $response->assertSee('09:00');
        $response->assertSee('18:00');
        $response->assertSee('1:00'); 
        $response->assertSee('8:00');
    }
}
