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

        $attendance = Attendance::with(['user', 'breaks', 'attendanceRequests.newBreaks']) 
        ->findOrFail($id);

        $pendingRequest = $attendance->attendanceRequests
        ->where('approval_status', '承認待ち')
        ->sortByDesc('request_date')->first();

        $approvedRequest = $attendance->attendanceRequests
        ->where('approval_status', '承認済み')
        ->sortByDesc('request_date')->first();

        /*$source = $pendingRequest ?? $approvedRequest;

        if ($source) {

            $referClockIn = $source->new_clock_in;
            $referClockOut = $source->new_clock_out;
            $referBreaks = collect($source->newBreaks ?? []);
            $referRemarks = $source->remarks;*/
        if($pendingRequest) {
            //$referClockIn = $pendingRequest->new_clock_in;
            //$referClockOut = $pendingRequest->new_clock_out;
            $source = $pendingRequest;
            $rawBreaks = $pendingRequest->newBreaks;
            //$referRemarks = $pendingRequest->remarks;
        } elseif ($approvedRequest && $approvedRequest->newBreaks->count() > 0) {
            //$referClockIn = $approvedRequest->new_clock_in;
            //$referClockOut = $approvedRequest->new_clock_out;
            $source = $approvedRequest;
            $rawBreaks = $approvedRequest->newBreaks;
            //$referRemarks = $approvedRequest->remarks;

        } else {
            //$referClockIn = $attendance->clock_in;
            //$referClockOut = $attendance->clock_out;
            $source = $attendance;
            $rawBreaks = $attendance->breaks;
            //$referRemarks = $attendance->remarks;
        }

        $referClockIn = $source->new_clock_in ?? $source->clock_in;
        $referClockOut = $source->new_clock_out ?? $source->clock_out;
        $referRemarks = $source->remarks ?? '';

        $referBreaks = $rawBreaks->map(function ($b) {
            return (object)[
                'start' => $b->new_break_in ?? $b->break_start,
                'end' => $b->new_break_out ?? $b->break_end,
            ];
        })->values();

        $date = \Carbon\Carbon::parse($attendance->date);
        $year = $date->year;

        return view('admin.attendance.show', [
            'attendance' =>$attendance,
            'pendingRequest' => $pendingRequest,
            'approvedRequest' => $approvedRequest,
            'hasPending' => !is_null($pendingRequest),
            'isApproved' => !is_null($approvedRequest),
            'date' => $date,
            'year' => $year,
            'referClockIn' => $referClockIn,
            'referClockOut' => $referClockOut,
            'referRemarks' => $referRemarks,
            'referBreaks' => $referBreaks,
        ]);
    }

    public function update(AdminShowRequest $request, $id)
{
    $attendance = Attendance::with('breaks')->findOrFail($id);
    $validated  = $request->validated();

    $formatTime = fn($t) => $t ? Carbon::parse($t)->format('H:i:s') : null;

    $isAdmin = Auth::guard('admin')->check();

    $attendanceRequest = AttendanceRequest::create([
        'attendance_id'  => $attendance->id,
        'user_id'        => $attendance->user_id,
        'approval_status'=> $isAdmin ? '承認済み' : '承認待ち',
        'request_date'   => now()->toDateString(),
        'new_date'       => $attendance->date,
        'new_clock_in'   => $formatTime($validated['clock_in']),
        'new_clock_out'  => $formatTime($validated['clock_out']),
        'remarks'        => $validated['remarks'] ?? '',
    ]);

    if ($request->filled('new_breaks')) {
        foreach ($request->new_breaks as $break) {
            if (!empty($break['in']) && !empty($break['out'])) {
                $attendanceRequest->newBreaks()->create([
                    'new_break_in'  => Carbon::parse($break['in'])->format('H:i:s'),
                    'new_break_out' => Carbon::parse($break['out'])->format('H:i:s'),
                ]);
            }
        }
    }

    if ($isAdmin) {
    
        $attendance->update([
            'clock_in' => $attendanceRequest->new_clock_in,
            'clock_out'=> $attendanceRequest->new_clock_out,
            'remarks'  => $attendanceRequest->remarks,
        ]);

        $attendance->breaks()->delete();

        foreach ($attendanceRequest->newBreaks as $nb) {
        $attendance->breaks()->create([
            'break_start' => $nb->new_break_in,
            'break_end'   => $nb->new_break_out,
        ]);
    }
        /*$existingBreaks = $attendance->breaks->values();

        foreach ($attendanceRequest->newBreaks as $index => $nb) {
            if (isset($existingBreaks[$index])) {
                $existingBreaks[$index]->update([
                    'break_start' => $nb->new_break_in,
                    'break_end' => $nb->new_break_out,
                ]);
            }else {
            $attendance->breaks()->create([
                'break_start' => $nb->new_break_in,
                'break_end'   => $nb->new_break_out,
            ]);
            }
        }*/

        $attendance->load('breaks');

        $totalBreakSeconds = 0;
        foreach ($attendance->breaks as $b) {
            $totalBreakSeconds += Carbon::parse($b->break_start)
                ->diffInSeconds(Carbon::parse($b->break_end));
        }

        $workSeconds = Carbon::parse($attendance->clock_in)
            ->diffInSeconds(Carbon::parse($attendance->clock_out));

        $attendance->update([
            'total_break_time' => gmdate('H:i:s', $totalBreakSeconds),
            'work_time'        => gmdate('H:i:s', max($workSeconds - $totalBreakSeconds, 0)),
        ]);
    }

    return redirect()
        ->route('admin.show', $attendance->id)
        ->with('success', $isAdmin ? '勤怠を修正しました' : '修正申請を送信しました');
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
