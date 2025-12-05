<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Attendance;
use App\Models\AttendanceRequest;
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

        $attendance = Attendance::with(['user', 'breaks', 'attendanceRequests'])
        ->findOrFail($id);

        $hasPending = $attendance->attendanceRequests
        ->where('approval_status', '承認待ち')
        ->isNotEmpty();

        $latestRequest = $attendance->attendanceRequests
        ->sortByDesc('id')
        ->first();

        $date = \Carbon\Carbon::parse($attendance->date);
        $year = $date->year;
        $month = $date->month;

        return view('admin.attendance.show', compact('attendance', 'year', 'month', 'date', 'hasPending', 'latestRequest'));
    }

    public function update(AdminShowRequest $request, $id)
    {
        $attendance = Attendance::with('breaks')->findOrFail($id);

        $validated = $request->validated();
        /*$user = Auth::user();
    
        $firstBreak = $attendance->breaks->first();
    
        $newClockIn  = $validated['clock_in'] ?? $attendance->clock_in;
        $newClockOut = $validated['clock_out'] ?? $attendance->clock_out;
        $newBreakIn  = $firstBreak ? $firstBreak->break_start : null;
        $newBreakOut = $firstBreak ? $firstBreak->break_end : null;*/
    
        $formatTime = fn($time) => $time ? Carbon::parse($time)->format('H:i:s') : null;
    
        $attendance->update([
            'clock_in' => $formatTime($validated['clock_in']), //?? $attendance->clock_in),
            'clock_out' => $formatTime($validated['clock_out']), //?? $attendance->clock_out),
            'remarks' => $validated['remarks'], //?? $attendance->remarks,
        ]);

        if ($attendance->breaks->first()) {
            $attendance->breaks->first()->update([
                'break_start' => $formatTime($validated['break_start']), //?? $attendance->breaks->first()->break_start),
                'break_end' => $formatTime($validated['break_end']), //?? $attendance->breaks->first()->break_end),
            ]);
        }
        /*AttendanceRequest::create([
            'attendance_id'   => $attendance->id,
            'user_id'         => $attendance->user_id,
            'approval_status' => '承認済み',
            'request_date'    => now()->toDateString(),
            'new_date'        => $attendance->date,
            'new_clock_in'    => $formatTime($validated['clock_in'] ?? null),
            'new_clock_out'   => $formatTime($validated['clock_out'] ?? null),
            'new_break_in'    => $formatTime($validated['break_start'] ?? null),
            'new_break_out'   => $formatTime($validated['break_end'] ?? null),
            'remarks'         => $validated['remarks'] ?? '',
        ]);*/
    
        return redirect()->route('admin.show', $attendance->id)
        ->with('success', '修正しました');
    }

    public function staffAttendance(Request $request, $user_id)
    {
        $user = User::findOrFail($user_id);

        $year = $request->query('year', now()->year);
        $month = $request->query('month', now()->month);

        $startOfMonth = Carbon::create($year, $month, 1)->startOfMonth();
        $endOfMonth = (clone $startOfMonth)->endOfMonth();

        $attendances = Attendance::where('user_id', $user->id)
        ->whereBetween('date' , [$startOfMonth->toDateString(), $endOfMonth->toDateString()])
        ->get()->keyBy('date');

        $dates = [];
        $date = $startOfMonth->copy();
        while ($date->lte($endOfMonth)) {
            $dates[] = $date->copy();
            $date->addDay();
        }

        return view('admin.attendance.staff_list', compact('user', 'year', 'month', 'dates', 'attendances'));
    }

    public function exportCsv($user_id, Request $request) {
        $year = $request->year;
        $month = $request->month;

        $user = User::findOrFail($user_id);

        $startDate = Carbon::create($year, $month)->startOfMonth();
        $endDate = Carbon::create($year, $month)->endOfMonth();

        $attendances = Attendance::where('user_id', $user_id)
        ->whereBetween('date', [$startDate, $endDate])
        ->orderBy('date')
        ->get();

        $fileName = "{$user->name}_{$year}年{$month}月勤怠.csv";

        $csv = "日付,出勤,退勤,休憩合計,勤務時間\n";

        foreach ($attendances as $attendance) {
            $csv .= implode(",", [
                $attendance->date,
                $attendance->clock_in ? Carbon::parse($attendance->clock_in)->format('H:i') : '',
                $attendance->clock_out ? Carbon::parse($attendance->clock_out)->format('H:i') : '',
                $attendance->total_break_time ? Carbon::parse($attendance->total_break_time)->format('G:i') : '',
                $attendance->work_time ? Carbon::parse($attendance->work_time)->format('G:i') : '',
            ]) . "\n";
        }
        $csv = "\xEF\xBB\xBF" . $csv;

        return response($csv)
        ->header('Content-Type', 'text/csv')
        ->header('Content-Disposition', "attachment; filename={$fileName}");
    }
}
