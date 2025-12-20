<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\AttendanceRequest;

class AttendanceRequestController extends Controller
{
    public function index(Request $request)
    {

        $status = $request->query('status', 'pending');

        $approvalStatus = $status === 'approved' ? '承認済み' : '承認待ち';

        $requests = AttendanceRequest::with('user')
        ->where('approval_status', $approvalStatus)
        ->orderByDesc('request_date')
        ->paginate(10);
       
        return view('admin.request.list', compact('requests', 'status'));
    }

    public function approveForm($id)
    {
        $requestData = AttendanceRequest::with('attendance.breaks', 'newBreaks', 'user')
        ->findOrFail($id);

        $attendance = $requestData->attendance;

        $hasNewBreaks = $requestData->newBreaks->count() > 0;
        $referBreaks  = $hasNewBreaks ? $requestData->newBreaks : $attendance->breaks;

        $referClockIn  = $requestData->new_clock_in  ?? $attendance->clock_in;
        $referClockOut = $requestData->new_clock_out ?? $attendance->clock_out;
        $referRemarks  = $requestData->remarks       ?? $attendance->remarks;

        $date = \Carbon\Carbon::parse($attendance->date);
        $year = $date->year;

        $format = fn($v) => $v ? Carbon::parse($v)->format('H:i') : '';

        $formattedClockIn  = $format($referClockIn);
        $formattedClockOut = $format($referClockOut);
        $formattedRemarks = $referRemarks;
        
        return view('admin.request.approve', compact(
        'requestData',
        'attendance',
        'year',
        'date',
        'formattedClockIn',
        'formattedClockOut',
        'formattedRemarks',
        'referBreaks',
    ));
}

    public function approve($id)
    {
        $requestData = AttendanceRequest::with('attendance.breaks', 'newBreaks')->findOrFail($id);

        $attendance = $requestData->attendance;

        $attendance->update([
            'clock_in' => $requestData->new_clock_in,
            'clock_out' => $requestData->new_clock_out,
            'remarks' => $requestData->remarks,
        ]);

        if ($requestData->newBreaks->count() > 0) {

        $attendance->breaks()->delete();

        foreach ($requestData->newBreaks as $newBreak) {
            $attendance->breaks()->create([
                'break_start' => $newBreak->new_break_in,
                'break_end' => $newBreak->new_break_out,
            ]);
        }
        }
        $attendance->refresh();

        $totalBreakMinutes = 0;

        foreach ($attendance->breaks as $break) {
            $start = Carbon::parse($break->break_start);
            $end = Carbon::parse($break->break_end);
            $totalBreakMinutes += $end->diffInMinutes($start);
        }

        $totalBreakTime = sprintf('%02d:%02d', floor($totalBreakMinutes / 60), $totalBreakMinutes % 60);

        $clockIn = Carbon::parse($attendance->clock_in);
        $clockOut = Carbon::parse($attendance->clock_out);

        $workMinutes = $clockOut->diffInMinutes($clockIn) - $totalBreakMinutes;

        $workTime = sprintf('%02d:%02d', floor($workMinutes / 60), $workMinutes % 60);

        $attendance->update([
            'total_break_time' => $totalBreakTime,
            'work_time' => $workTime,
        ]);

        $requestData->update([
            'approval_status' => '承認済み'
        ]);

        return redirect()
        ->back();
    }
}
