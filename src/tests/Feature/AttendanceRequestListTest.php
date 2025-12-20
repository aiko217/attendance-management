<?php

namespace Tests\Feature\Attendance;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\AttendanceRequest;

class AttendanceRequestListTest extends TestCase
{
    use RefreshDatabase;

    public function test_pending_requests_are_displayed()
    {
        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
        ]);

        AttendanceRequest::create([
            'user_id' => $user->id,
            'attendance_id' => $attendance->id,
            'approval_status' => '承認待ち',
            'new_date' => '2025-01-01',
            'new_clock_in' => '09:00:00',
            'new_clock_out' => '18:00:00',
            'request_date' => now(),
            'remarks' => '修正申請テスト',
        ]);

        $response = $this->actingAs($user)
            ->get(route('stamp_correction_request.list'));

        $response->assertStatus(200);
        $response->assertSee('承認待ち');
        $response->assertSee('修正申請テスト');
        $response->assertSee('2025/01/01');
    }

    public function test_approved_requests_are_displayed()
    {
        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
        ]);

        AttendanceRequest::create([
            'user_id' => $user->id,
            'attendance_id' => $attendance->id,
            'approval_status' => '承認済み',
            'new_date' => '2025-02-01',
            'new_clock_in' => '10:00:00',
            'new_clock_out' => '19:00:00',
            'request_date' => now(),
            'remarks' => '承認済み申請',
        ]);

        $response = $this->actingAs($user)
            ->get(route('stamp_correction_request.list', ['status' => 'approved']));

        $response->assertStatus(200);
        $response->assertSee('承認済み');
        $response->assertSee('承認済み申請');
        $response->assertSee('2025/02/01');
    }

    public function test_click_detail_moves_to_attendance_detail()
    {
        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
        ]);

        AttendanceRequest::create([
            'user_id' => $user->id,
            'attendance_id' => $attendance->id,
            'approval_status' => '承認待ち',
            'new_date' => '2025-01-01',
            'new_clock_in' => '09:00:00',
            'new_clock_out' => '18:00:00',
            'request_date' => now(),
            'remarks' => '詳細テスト',
        ]);

        $response = $this->actingAs($user)
            ->get(route('attendance.show', $attendance->id));

        $response->assertStatus(200);
    }
}
