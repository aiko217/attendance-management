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
        ->with(['user', 'breaks', 'attendanceRequests.newBreaks'])
        ->where('id', $id)
        ->firstOrFail();

        $pendingRequest= $attendance->attendanceRequests
        ->where('approval_status', '承認待ち')->sortByDesc('request_date')
        ->first();

        $approvedRequest = $attendance->attendanceRequests
        ->where('approval_status', '承認済み')->sortByDesc('request_date')
        ->first();

        $source = $pendingRequest ?? $approvedRequest;

        if ($source) {

            $referClockIn = $source->new_clock_in;
            $referClockOut = $source->new_clock_out;
            $referBreaks = $source->newBreaks;
            $referRemarks = $source->remarks;
        } else {
            $referClockIn = $attendance->clock_in;
            $referClockOut = $attendance->clock_out;
            $referBreaks = $attendance->breaks;
            $referRemarks = $attendance->remarks;
        }

        $date = \Carbon\Carbon::parse($attendance->date);
        $year = $date->year;

        return view('attendance.show', [
            'attendance' =>$attendance,
            'pendingRequest' => $pendingRequest,
            'approvedRequest' => $approvedRequest,
            'hasPending' => !is_null($pendingRequest),
            'isApproved' => false,
            'date' => $date,
            'year' => $year,
            'referClockIn' => $referClockIn,
            'referClockOut' => $referClockOut,
            'referRemarks' => $referRemarks,
            'referBreaks' => $referBreaks,
        ]);
        
    }

    public function update(ShowRequest $request, $id)
    {

        $attendance = Attendance::with('breaks')->findOrFail($id);
        $validated = $request->validated();
        $user = Auth::user();
    
        $formatTime = fn($time) => $time ? Carbon::parse($time)->format('H:i:s') : null;
    
        $attendanceRequest = AttendanceRequest::create([
            'attendance_id' => $attendance->id,
            'user_id' => $user->id,
            'approval_status' => '承認待ち',
            'request_date' => now()->toDateString(),
            'new_date' => $attendance->date,
            'new_clock_in' => $formatTime($validated['clock_in']),
            'new_clock_out' => $formatTime($validated['clock_out']),
            'remarks' => $validated['remarks'] ?? '',
        ]);

        if ($request->has('new_breaks')) {
        foreach ($request->new_breaks as $breakData) {
            if (!empty($breakData['in']) && !empty($breakData['out'])) {
                $attendanceRequest->newBreaks()->create([
                    'new_break_in' => $breakData['in'],
                    'new_break_out' => $breakData['out'],
                ]);
            }
        }
    }
    
        return redirect()->route('attendance.show', $attendance->id)
        ->withInput();
    }
}
