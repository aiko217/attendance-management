<?php

namespace Tests\Feature\Attendance;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Attendance;

class AttendanceUpdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_clock_in_after_clock_out_shows_error()
    {
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->put(
            route('attendance.update', $attendance->id),
            [
                'clock_in' => '19:00',
                'clock_out' => '18:00',
                'remarks' => '修正',
            ]
        );

        $response->assertSessionHasErrors('clock_in');
    }

    public function test_break_start_after_clock_out_shows_error()
    {
        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'clock_out' => '18:00:00',
        ]);

        $response = $this->actingAs($user)->put(
            route('attendance.update', $attendance->id),
            [
                'clock_out' => '18:00',
                'new_breaks' => [
                    ['in' => '19:00'],
                ],
                'remarks' => '修正',
            ]
        );

        $response->assertSessionHasErrors('new_breaks.0.in');
    }

    public function test_remarks_required_validation()
    {
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->put(
            route('attendance.update', $attendance->id),
            []
        );

        $response->assertSessionHasErrors('remarks');
    }

    public function test_update_request_is_created()
    {
        $user = User::factory()->create();

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => '2025-01-01',
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
            'attendance_status' => '退勤済',
        ]);

        $response = $this->actingAs($user)->put(
            route('attendance.update', $attendance->id),
            [
                'clock_in' => '09:30',
                'clock_out' => '18:00',
                'remarks' => '修正申請です',
            ]
        );

        $response->assertRedirect();

        $this->assertDatabaseHas('attendance_requests', [
            'attendance_id' => $attendance->id,
            'user_id' => $user->id,
            'remarks' => '修正申請です',
            'approval_status' => '承認待ち',
        ]);
    }
}
