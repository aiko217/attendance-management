<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Requests\Admin\AdminShowRequest;

class AttendanceController extends Controller
{
    public function index() {
        $date = request('date') ? Carbon::parse(request('date')) : Carbon::today();

        $attendances = Attendance::with(['user', 'breaks'])
        ->whereDate('date', $date->toDateString())
        ->get();

        return view('admin.attendance.list', compact('attendances', 'date'));
    }
    
    public function show($id)
    {
        //$user = Auth::user();

        $attendance = Attendance::with(['user', 'breaks', 'attendanceRequests'])
        ->findOrFail($id);

        $hasPending = $attendance->attendanceRequests
        ->where('approval_status', '承認待ち')
        ->isNotEmpty();

        $date = \Carbon\Carbon::parse($attendance->date);
        $year = $date->year;
        $month = $date->month;

        return view('admin.attendance.show', compact('attendance', 'year', 'month', 'date', 'hasPending'));
    }

    public function update(AdminShowRequest $request, $id)
    {
        $attendance = Attendance::with('breaks', 'attendanceRequests')->findOrFail($id);

        $validated = $request->validated();
        $user = Auth::user();
    
        $firstBreak = $attendance->breaks->first();
    
        $newClockIn  = $validated['clock_in'] ?? $attendance->clock_in;
        $newClockOut = $validated['clock_out'] ?? $attendance->clock_out;
        $newBreakIn  = $firstBreak ? $firstBreak->break_start : null;
        $newBreakOut = $firstBreak ? $firstBreak->break_end : null;
    
        $formatTime = fn($time) => $time ? Carbon::parse($time)->format('H:i:s') : null;
    
        AttendanceRequest::create([
            'attendance_id'   => $attendance->id,
            'user_id'         => $user->id,
            'approval_status' => '承認待ち',
            'request_date'    => now()->toDateString(),
            'new_date'        => $attendance->date,
            'new_clock_in'    => $formatTime($validated['clock_in'] ?? null),
            'new_clock_out'   => $formatTime($validated['clock_out'] ?? null),
            'new_break_in'    => $formatTime($validated['break_start'] ?? null),
            'new_break_out'   => $formatTime($validated['break_end'] ?? null),
            'remarks'         => $validated['remarks'] ?? '',
        ]);
    
        return redirect()->route('admin.attendance.show', $attendance->id)
        ->withInput();
    }
}
