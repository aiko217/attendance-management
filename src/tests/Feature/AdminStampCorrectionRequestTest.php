<?php

namespace Tests\Feature\Admin;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\AttendanceRequest;
use App\Http\Middleware\AdminMiddleware;
use Illuminate\Auth\Middleware\Authenticate;

class AdminStampCorrectionRequestTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware();
    }

    public function test_pending_requests_are_displayed()
    {
        $admin = User::factory()->create(['admin_status' => 1]);
        $user  = User::factory()->create();

        $attendance = Attendance::factory()->create(['user_id' => $user->id]);

        AttendanceRequest::create([
            'user_id' => $user->id,
            'attendance_id' => $attendance->id,
            'approval_status' => '承認待ち',
            'request_date' => now(),
            'new_date' => '2025-01-10',
            'new_clock_in' => '09:30:00',
            'new_clock_out' => '18:30:00',
            'remarks' => '修正申請テスト',
        ]);

        $response = $this->actingAs($admin)
            ->get(route('admin.stamp_correction_request.list'));

        $response->assertStatus(200);
        $response->assertSee('修正申請テスト');
    }

    public function test_approved_requests_are_displayed()
    {
        $admin = User::factory()->create(['admin_status' => 1]);
        $user  = User::factory()->create();

        $attendance = Attendance::factory()->create(['user_id' => $user->id]);

        AttendanceRequest::create([
            'user_id' => $user->id,
            'attendance_id' => $attendance->id,
            'approval_status' => '承認済み',
            'request_date' => now(),
            'new_date' => '2025-01-11',
            'new_clock_in' => '10:00:00',
            'new_clock_out' => '19:00:00',
            'remarks' => '承認済み申請',
        ]);

        $response = $this->actingAs($admin)
            ->get(route('admin.stamp_correction_request.list', ['status' => 'approved']));

        $response->assertStatus(200);
        $response->assertSee('承認済み申請');
    }

    public function test_request_detail_is_displayed_correctly()
    {
        $admin = User::factory()->create(['admin_status' => 1]);
        $user  = User::factory()->create(['name' => '山田太郎']);

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => '2025-01-01',
        ]);

        $request = AttendanceRequest::create([
            'user_id' => $user->id,
            'attendance_id' => $attendance->id,
            'approval_status' => '承認待ち',
            'request_date' => now(),
            'new_date' => '2025-01-01',
            'new_clock_in' => '09:30:00',
            'new_clock_out' => '18:30:00',
            'remarks' => '修正理由',
        ]);

        $response = $this->actingAs($admin)
            ->get(route('approveForm', $request->id));

        $response->assertStatus(200);
        $response->assertSee('山田太郎');
        $response->assertSee('09:30');
        $response->assertSee('18:30');
        $response->assertSee('修正理由');
    }

    public function test_request_is_approved()
    {
        $admin = User::factory()->create(['admin_status' => 1]);
        $user  = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
        ]);

        $request = AttendanceRequest::create([
            'user_id' => $user->id,
            'attendance_id' => $attendance->id,
            'approval_status' => '承認待ち',
            'request_date' => now(),
            'new_date' => '2025-01-02',
            'new_clock_in' => '09:30:00',
            'new_clock_out' => '18:30:00',
            'remarks' => '承認テスト',
        ]);

        $response = $this->actingAs($admin)
            ->post(route('admin.stamp_correction_request.approve', $request->id));

        $response->assertRedirect();

        $this->assertDatabaseHas('attendance_requests', [
            'id' => $request->id,
            'approval_status' => '承認済み',
        ]);
    }
}