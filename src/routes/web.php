<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AttendanceRequestController;
use App\Http\Controllers\Admin\AuthController as AdminAuthController;
use App\Http\Controllers\Admin\AttendanceController as AdminAttendanceController;
use App\Http\Controllers\Admin\AttendanceRequestController as AdminAttendanceRequestController;
use App\Http\Controllers\Admin\StaffController
as AdminStaffController;
use 
Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
Route::get('/email/verify', function () {
    return view('auth.verify-email');
})->middleware('auth')->name('verification.notice');
Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
    $request->fulfill();
    return redirect('/attendance');
})->middleware(['auth', 'signed'])->name('verification.verify');
Route::post('/email/verification-notification', function (Request $request) {
    $request->user()->sendEmailVerificationNotification();
    return back()->with('message', '認証メールを再送しました。');
})->middleware(['auth', 'throttle:6,1'])->name('verification.send');

Route::post('/register', [AuthController::class, 'store']);
Route::get('/login', [AuthController::class, 'loginForm'])->name('login.form');
Route::post('/login', [AuthController::class, 'login'])->name('login');
Route::get('/admin/login', [AdminAuthController::class, 'loginForm'])->name('admin.login.form');
Route::post('/admin/login', [AdminAuthController::class, 'login'])->name('admin.login');

Route::middleware('auth')->group(function () {
    Route::get('/attendance', [AttendanceController::class, 'index'])->name('attendance.index');
    Route::post('/attendance/clock-in', [AttendanceController::class, 'clockIn'])->name('attendance.clockIn');
    Route::post('/attendance/break-start', [AttendanceController::class, 'breakStart'])->name('attendance.breakStart');
    Route::post('/attendance/break-end', [AttendanceController::class, 'breakEnd'])->name('attendance.breakEnd');
    Route::post('/attendance/clock-out', [AttendanceController::class, 'clockOut'])->name('attendance.clockOut');
    Route::post('/logout', [AuthController::class, 'destroy'])->name('logout');
    Route::get('/attendance/list', [AttendanceController::class, 'list'])->name('attendance.list');
    Route::get('/attendance/detail/{id}', [AttendanceController::class, 'show'])->name('attendance.show');
    Route::put('/attendance/detail/{id}', [AttendanceController::class, 'update'])->name('attendance.update');
    Route::get('/stamp_correction_request/list', [AttendanceRequestController::class, 'index'])->name('stamp_correction_request.list');
    //Route::get('/stamp_correction_request/detail/{id}',
    //[AttendanceRequestController::class, 'detail']
    //)->name('stamp_correction_request.detail');
});
Route::middleware('auth:admin')->group(function () {
    Route::post('/admin/logout', [AdminAuthController::class, 'destroy'])->name('admin.logout');
    Route::get('/admin/attendance/list', [AdminAttendanceController::class, 'index'])->name('admin.index');
    Route::get('/admin/attendance/{id}', [AdminAttendanceController::class, 'show'])->name('admin.show');
    Route::put('/admin/attendance/detail/{id}', [AdminAttendanceController::class, 'update'])->name('admin.update');
    Route::get('/admin/staff/list', [AdminStaffController::class, 'staffList'])->name('admin.staff.list');
    Route::get('/admin/attendance/staff/{user_id}', [AdminAttendanceController::class, 'staffAttendance'])->name('admin.staff.attendance_list');
    Route::get('/admin/attendance/staff/{user_id}/csv', [AdminAttendanceController::class, 'exportCsv'])->name('admin.staff.attendance_csv');
    Route::get('/admin/stamp_correction_request/list', [AdminAttendanceRequestController::class, 'index'])->name('admin.stamp_correction_request.list');
    Route::get('/stamp_correction_request/approve/{attendance_correct_request_id}',
    [AdminAttendanceRequestController::class, 'approveForm'])->name('approveForm');
    Route::post('/stamp_correction_request/approve/{attendance_correct_request_id}',
    [AdminAttendanceRequestController::class, 'approve'])->name('admin.stamp_correction_request.approve');
});    


