<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\BreakTime;
use App\Models\AttendanceRequest;
use Illuminate\Http\Request;
use App\Http\Requests\ShowRequest;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $today = Carbon::today()->toDateString();

        $attendance = Attendance::where('user_id', $user->id)
        ->where('date', $today)
        ->first();

        return view('attendance.index', compact('attendance'));
    }

    public function clockIn()
    {
        $user = Auth::user();
        $today = Carbon::today()->toDateString();
        
        $attendance = Attendance::firstOrCreate(
            ['user_id' => $user->id, 'date' => $today],
            [
            'clock_in' => Carbon::now()->format('H:i:s'),
            'clock_out' => null,
            'attendance_status' => '出勤中']
        );
        return back();
    }

    public function breakStart()
    {
        $attendance = $this->getTodayAttendance();

        if ($attendance && $attendance->attendance_status === '出勤中') {
            BreakTime::create([
                'attendance_id' => $attendance->id,
                'break_start' => Carbon::now()->format('H:i:s')
            ]);
            $attendance->update(['attendance_status' => '休憩中']);
        }
        return back();
    }

    public function breakEnd()
    {
        $attendance = $this->getTodayAttendance();

        if ($attendance && $attendance->attendance_status === '休憩中') {
            $break = $attendance->breaks()->latest()->first();
            if ($break && !$break->break_end) {
                $break->update(['break_end' => Carbon::now()->format('H:i:s')]);
            }
            $attendance->update(['attendance_status' => '出勤中']);
        }
        return back();
    }

    public function clockOut()
    {
        $attendance = $this->getTodayAttendance();

        if ($attendance && $attendance->attendance_status === '出勤中') {
            $attendance->update([
                'clock_out' => Carbon::now()->format('H:i:s'),
                'attendance_status' => '退勤済',
            ]);

            $this->calculateWorkTime($attendance);
        }
        return back();
    }

    public function getTodayAttendance()
    {
        $user = Auth::user();
        $today = Carbon::today()->toDateString();

        return Attendance::where('user_id', $user->id)
        ->where('date', $today)->first();
    }

    private function calculateWorkTime($attendance)
    {
        $clockIn = Carbon::parse($attendance->clock_in);
        $clockOut = Carbon::parse($attendance->clock_out);

        $totalBreakSeconds = 0;
        foreach ($attendance->breaks as $break) {
            if ($break->break_end) {
                $totalBreakSeconds += Carbon::parse($break->break_end)->diffInSeconds(Carbon::parse($break->break_start));
            }
        }

        $totalWorkSeconds = $clockOut->diffInSeconds($clockIn) - $totalBreakSeconds;

        $attendance->update([
            'total_break_time' => gmdate('H:i:s', $totalBreakSeconds),
            'work_time' => gmdate('H:i:s', $totalWorkSeconds),
        ]);
    } 
        
    public function list(Request $request)
    {
        $user = Auth::user();

        $year = $request->query('year', now()->year);
        $month = $request->query('month', now()->month);

        $startOfMonth = Carbon::create($year, $month, 1)->startOfMonth();
        $endOfMonth = (clone $startOfMonth)->endOfMonth();

        $attendances = Attendance::where('user_id', $user->id)->whereBetween('date' , [$startOfMonth->toDateString(), $endOfMonth->toDateString()])->get()->keyBy('date');

        $dates = [];
        $date = $startOfMonth->copy();
        while ($date->lte($endOfMonth)) {
            $dates[] = $date->copy();
            $date->addDay();
        }

        return view('attendance.list', compact('attendances', 'dates', 'year', 'month'));
    }

    public function show($id)
    {
        $user = Auth::user();

        $attendance = Attendance::where('user_id', $user->id)
        ->with(['user', 'breaks', 'attendanceRequests'])
        ->where('id', $id)
        ->firstOrFail();

        $hasPending = $attendance->attendanceRequests
        ->where('approval_status', '承認待ち')
        ->isNotEmpty();

        $date = \Carbon\Carbon::parse($attendance->date);
        $year = $date->year;
        $month = $date->month;

        return view('attendance.show', compact('attendance', 'year', 'month', 'date', 'hasPending'));
    }

    public function update(ShowRequest $request, $id)
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
            'new_clock_in'    => $formatTime($newClockIn),
            'new_clock_out'   => $formatTime($newClockOut),
            'new_break_in'    => $formatTime($newBreakIn),
            'new_break_out'   => $formatTime($newBreakOut),
            'remarks'         => $validated['remarks'] ?? '',
        ]);
    
        return redirect()->route('attendance.show', $attendance->id)
        ->withInput();
    }
}
