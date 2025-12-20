<?php

namespace Tests\Feature\Attendance;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakTime;

class AttendanceDetailTest extends TestCase
{
    use RefreshDatabase;

    public function test_detail_page_displays_logged_in_user_name()
    {
        $user = User::factory()->create(['name' => '山田太郎']);

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => '2025-01-01',
        ]);

        $response = $this->actingAs($user)
            ->get(route('attendance.show', $attendance->id));

        $response->assertSee('山田太郎');
    }

    public function test_detail_page_displays_selected_date()
    {
        $user = User::factory()->create();

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => '2025-01-01',
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
            'attendance_status' => '退勤済',
        ]);

        BreakTime::create([
            'attendance_id' => $attendance->id,
            'break_start' => '12:00:00',
            'break_end' => '13:00:00',
        ]);

        $response = $this->actingAs($user)
            ->get(route('attendance.show', $attendance->id));

        $response->assertStatus(200);

        $response->assertSee($user->name);
        $response->assertSee('2025年');
        $response->assertSee('01月01日');
        $response->assertSee('09:00');
        $response->assertSee('18:00');
        $response->assertSee('12:00');
        $response->assertSee('13:00'); 
    }

    public function test_clock_in_and_out_time_are_displayed()
    {
        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
        ]);

        $this->actingAs($user)
            ->get(route('attendance.show', $attendance->id))
            ->assertSee('09:00')
            ->assertSee('18:00');
    }

    public function test_break_times_are_displayed()
    {
        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
        ]);

        BreakTime::create([
            'attendance_id' => $attendance->id,
            'break_start' => '12:00:00',
            'break_end' => '13:00:00',
        ]);

        $this->actingAs($user)
            ->get(route('attendance.show', $attendance->id))
            ->assertSee('12:00')
            ->assertSee('13:00');
    }
}
