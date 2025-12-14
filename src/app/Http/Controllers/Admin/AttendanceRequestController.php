<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
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

    // 表示用のデータ
        $referClockIn  = $requestData->new_clock_in  ?? $attendance->clock_in;
        $referClockOut = $requestData->new_clock_out ?? $attendance->clock_out;
        $referRemarks  = $requestData->remarks       ?? $attendance->remarks;
        /*$referBreaks   = $hasNewBreaks
        ? $requestData->newBreaks
        : $attendance->breaks;*/

        /*if ($status === '承認待ち') {
            $referClockIn  = $requestData->new_clock_in;
            $referClockOut = $requestData->new_clock_out;
            $referBreaks   = $requestData->newBreaks;
            $referRemarks  = $requestData->remarks;
        } 
        elseif ($status === '承認済み') {
            $referClockIn  = $attendance->clock_in;
            $referClockOut = $attendance->clock_out;
            $referBreaks   = $attendance->breaks;
            $referRemarks  = $attendance->remarks;
        }
        else {
            $referClockIn  = $attendance->clock_in;
            $referClockOut = $attendance->clock_out;
            $referRemarks  = $attendance->remarks;
            $referBreaks   = $attendance->breaks;
        }*/

        $date = \Carbon\Carbon::parse($attendance->date);
        $year = $date->year;

    // === 時間整形 ===
        $format = fn($v) => $v ? Carbon::parse($v)->format('H:i') : '';

        $formattedClockIn  = $format($referClockIn);
        $formattedClockOut = $format($referClockOut);
        $formattedRemarks = $referRemarks;

        /*$formattedBreak1Start = isset($referBreaks[0]) ? $format($referBreaks[0]->new_break_in  ?? $referBreaks[0]->break_start) : '';
        $formattedBreak1End   = isset($referBreaks[0]) ? $format($referBreaks[0]->new_break_out ?? $referBreaks[0]->break_end) : '';

    // 休憩2
        $formattedBreak2Start = isset($referBreaks[1]) ? $format($referBreaks[1]->new_break_in  ?? $referBreaks[1]->break_start) : '';
        $formattedBreak2End   = isset($referBreaks[1]) ? $format($referBreaks[1]->new_break_out ?? $referBreaks[1]->break_end) : '';*/
        

        return view('admin.request.approve', compact(
        'requestData',
        'attendance',
        'year',
        'date',
        'formattedClockIn',
        'formattedClockOut',
        /*'formattedBreak1Start',
        'formattedBreak1End',
        'formattedBreak2Start',
        'formattedBreak2End',*/
        'formattedRemarks',
        'referBreaks',
    ));
}

        /*if ($pending || $approved) {
            $referClockIn  = $requestData->new_clock_in;
            $referClockOut = $requestData->new_clock_out;
            $referBreaks   = $requestData->newBreaks;
            $referRemarks  = $requestData->remarks;
        } else {
            $referClockIn  = $attendance->clock_in;
            $referClockOut = $attendance->clock_out;
            $referBreaks   = $attendance->breaks;
            $referRemarks  = $attendance->remarks;
        }

        $date = \Carbon\Carbon::parse($attendance->date);
        $year = $date->year;

        $formattedClockIn  = $referClockIn  ? Carbon::parse($referClockIn)->format('H:i') : '';
        $formattedClockOut = $referClockOut ? Carbon::parse($referClockOut)->format('H:i') : '';

        $formattedBreak1Start = isset($referBreaks[0]) ? Carbon::parse($referBreaks[0]->new_break_in  ?? $referBreaks[0]->break_start)->format('H:i') : '';
        $formattedBreak1End   = isset($referBreaks[0]) ? Carbon::parse($referBreaks[0]->new_break_out ?? $referBreaks[0]->break_end)->format('H:i') : '';

        $formattedBreak2Start = isset($referBreaks[1]) ? Carbon::parse($referBreaks[1]->new_break_in  ?? $referBreaks[1]->break_start)->format('H:i') : '';
        $formattedBreak2End   = isset($referBreaks[1]) ? Carbon::parse($referBreaks[1]->new_break_out ?? $referBreaks[1]->break_end)->format('H:i') : '';
        $formattedRemarks = $referRemarks ?? '';

        

        return view('admin.request.approve', compact(
            'requestData',
            'attendance',
            'year',
            'date',
            'formattedClockIn',
            'formattedClockOut',
            'formattedBreak1Start',
            'formattedBreak1End',
            'formattedBreak2Start',
            'formattedBreak2End',
            'formattedRemarks',
            'referBreaks',
            'pending'
        ));
    }*/

    private function safeTime($value)
    {
        return $value ? Carbon::parse($value)->format('H:i') : '';
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
