<?php

namespace Tests\Feature\Admin;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Attendance;
use App\Http\Middleware\AdminMiddleware;
use Illuminate\Auth\Middleware\Authenticate;
use Carbon\Carbon;

class AdminAttendanceListTest extends TestCase
{
    use RefreshDatabase;

    /**
     * その日の全ユーザーの勤怠が表示される
     */
    public function test_admin_can_see_all_users_attendance_for_the_day()
    {
        Carbon::setTestNow('2025-01-10');

        $admin = User::factory()->create(['admin_status' => 1]);

        $user1 = User::factory()->create(['name' => '山田太郎']);
        $user2 = User::factory()->create(['name' => '佐藤花子']);

        Attendance::create([
            'user_id' => $user1->id,
            'date' => '2025-01-10',
            'clock_in' => '09:00:00',
            'attendance_status' => '退勤済',
        ]);

        Attendance::create([
            'user_id' => $user2->id,
            'date' => '2025-01-10',
            'clock_in' => '10:00:00',
            'attendance_status' => '退勤済',
        ]);

        $response = $this->actingAs($admin, 'admin')
            ->get(route('admin.index', ['date' => '2025-01-10']));

        $response->assertStatus(200);
        $response->assertSee('山田太郎');
        $response->assertSee('佐藤花子');
        $response->assertSee('09:00');
        $response->assertSee('10:00');
    }

    /**
     * 遷移時に現在の日付が表示される
     */
    public function test_today_date_is_displayed()
    {
        Carbon::setTestNow('2025-02-05');

        $admin = User::factory()->create(['admin_status' => 1]);

        $response = $this->actingAs($admin, 'admin')
            ->get(route('admin.index'));

        $response->assertStatus(200);
        $response->assertSee('2025/02/05');
        $response->assertSee('2025年02月05日の勤怠');
    }

    /**
     * 前日を指定すると前日の勤怠が表示される
     */
    public function test_previous_day_attendance_is_displayed()
    {
        $admin = User::factory()->create(['admin_status' => 1]);
        $user = User::factory()->create();

        Attendance::create([
            'user_id' => $user->id,
            'date' => '2025-01-09',
            'clock_in' => '08:30:00',
            'attendance_status' => '出勤中',
        ]);

        Attendance::create([
            'user_id' => $user->id,
            'date' => '2025-01-10',
            'clock_in' => '09:30:00',
            'attendance_status' => '出勤中',
        ]);

        $response = $this->actingAs($admin, 'admin')
            ->get(route('admin.index', ['date' => '2025-01-09']));

        $response->assertSee('08:30');
        $response->assertDontSee('09:30');
    }

    /**
     * 翌日を指定すると翌日の勤怠が表示される
     */
    public function test_next_day_attendance_is_displayed()
    {
        $admin = User::factory()->create(['admin_status' => 1]);
        $user = User::factory()->create();

        Attendance::create([
            'user_id' => $user->id,
            'date' => '2025-01-11',
            'clock_in' => '07:45:00',
            'attendance_status' => '出勤中',
        ]);

        $response = $this->actingAs($admin, 'admin')
            ->get(route('admin.index', ['date' => '2025-01-11']));

        $response->assertStatus(200);
        $response->assertSee('07:45');
    }
}
