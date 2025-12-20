<?php

namespace Tests\Feature\Admin;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;
use App\Http\Middleware\AdminMiddleware;
use Illuminate\Auth\Middleware\Authenticate;

class AdminUserAttendanceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware();
    }

    public function test_admin_can_see_all_users_name_and_email()
    {
        $admin = User::factory()->create(['admin_status' => 1]);

        $user = User::factory()->create([
            'name' => '山田太郎',
            'email' => 'taro@example.com',
            'admin_status' => 0,
        ]);

        $response = $this->actingAs($admin)
            ->get(route('admin.staff.list'));

        $response->assertStatus(200);
        $response->assertSee('山田太郎');
        $response->assertSee('taro@example.com');
    }

    public function test_user_attendance_is_displayed_correctly()
    {
        $admin = User::factory()->create(['admin_status' => 1]);
        $user = User::factory()->create(['name' => '山田太郎']);

        Attendance::create([
            'user_id' => $user->id,
            'date' => '2025-01-10',
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
            'total_break_time' => '01:00:00',
            'work_time' => '08:00:00',
            'attendance_status' => '退勤済',
        ]);

        $response = $this->actingAs($admin)
            ->get(route('admin.staff.attendance_list', [
                'user_id' => $user->id,
                'year' => 2025,
                'month' => 1,
            ]));

        $response->assertStatus(200);
        $response->assertSee('09:00');
        $response->assertSee('18:00');
        $response->assertSee('1:00');
        $response->assertSee('8:00');
    }

    public function test_previous_month_attendance_is_displayed()
    {
        $admin = User::factory()->create(['admin_status' => 1]);
        $user = User::factory()->create();

        Attendance::create([
            'user_id' => $user->id,
            'date' => '2025-01-05',
            'clock_in' => '09:00:00',
            'attendance_status' => '出勤中',
        ]);

        Attendance::create([
            'user_id' => $user->id,
            'date' => '2025-02-05',
            'clock_in' => '10:00:00',
            'attendance_status' => '出勤中',
        ]);

        $response = $this->actingAs($admin)
            ->get(route('admin.staff.attendance_list', [
                'user_id' => $user->id,
                'year' => 2025,
                'month' => 1,
            ]));

        $response->assertSee('09:00');
        $response->assertDontSee('10:00');
    }

    public function test_next_month_attendance_is_displayed()
    {
        $admin = User::factory()->create(['admin_status' => 1]);
        $user = User::factory()->create();

        Attendance::create([
            'user_id' => $user->id,
            'date' => '2025-03-10',
            'clock_in' => '08:30:00',
            'attendance_status' => '出勤中',
        ]);

        $response = $this->actingAs($admin)
            ->get(route('admin.staff.attendance_list', [
                'user_id' => $user->id,
                'year' => 2025,
                'month' => 3,
            ]));

        $response->assertStatus(200);
        $response->assertSee('08:30');
    }

    public function test_click_detail_moves_to_attendance_detail_page()
    {
        $admin = User::factory()->create(['admin_status' => 1]);
        $user = User::factory()->create();

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => '2025-01-01',
            'clock_in' => '09:00:00',
            'attendance_status' => '出勤中',
        ]);

        $response = $this->actingAs($admin)
            ->get(route('admin.show', $attendance->id));

        $response->assertStatus(200);
        $response->assertSee('09:00');
    }
}
