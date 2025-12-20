<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakTime;
use Carbon\Carbon;

class AttendanceListTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_sees_only_own_attendance_list()
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        Attendance::create([
            'user_id' => $user->id,
            'date' => '2025-01-01',
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
            'attendance_status' => '退勤済',
        ]);

        Attendance::create([
            'user_id' => $otherUser->id,
            'date' => '2025-01-01',
            'clock_in' => '10:00:00',
            'clock_out' => '19:00:00',
            'attendance_status' => '退勤済',
        ]);

        $response = $this->actingAs($user)
            ->get('/attendance/list?year=2025&month=1');

        $response->assertSee('09:00');
        $response->assertDontSee('10:00');
    }

    public function test_current_month_is_displayed_by_default()
    {
        Carbon::setTestNow('2025-03-15');

        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->get('/attendance/list');

        $response->assertSee('2025/03');
    }

    public function test_previous_month_attendance_is_displayed()
    {
        $user = User::factory()->create();

        Attendance::create([
            'user_id' => $user->id,
            'date' => '2025-02-10',
            'clock_in' => '09:00:00',
            'attendance_status' => '出勤中',
        ]);

        Attendance::create([
            'user_id' => $user->id,
            'date' => '2025-03-10',
            'clock_in' => '10:00:00',
            'attendance_status' => '出勤中',
        ]);

        $response = $this->actingAs($user)
            ->get('/attendance/list?year=2025&month=2');

        $response->assertSee('09:00');
        $response->assertDontSee('10:00');
    }

    public function test_next_month_attendance_is_displayed()
    {
        $user = User::factory()->create();

        Attendance::create([
            'user_id' => $user->id,
            'date' => '2025-04-05',
            'clock_in' => '08:30:00',
            'attendance_status' => '出勤中',
        ]);

        $response = $this->actingAs($user)
            ->get('/attendance/list?year=2025&month=4');

        $response->assertSee('08:30');
    }

    public function test_click_detail_moves_to_attendance_detail_page()
    {
        $user = User::factory()->create();

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => '2025-01-01',
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
            'attendance_status' => '退勤済',
        ]);

        $response = $this->actingAs($user)
            ->get(route('attendance.show', $attendance->id));

        $response->assertStatus(200);
        $response->assertSee('09:00');
        $response->assertSee('18:00');
    }
}
