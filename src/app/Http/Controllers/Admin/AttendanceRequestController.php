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
        $requestData = AttendanceRequest::with('attendance', 'user')
        ->findOrFail($id);

        $attendance = $requestData->attendance;

        $date = \Carbon\Carbon::parse($attendance->date);
        $year = $date->year;
        $hasPending = $requestData->approval_status === '承認待ち';

        return view('admin.request.approve', compact(
            'requestData',
            'attendance',
            'year',
            'date',
            'hasPending'
        ));
    }

    public function approve($id)
    {
        $requestData = AttendanceRequest::with('attendance', 'newBreaks')->findOrFail($id);

        $attendance = $requestData->attendance;

        $attendance->update([
            'clock_in' => $requestData->new_clock_in,
            'clock_out' => $requestData->new_clock_out,
            /*'break_start' => $requestData->new_break_start,
            'break_end' => $requestData->new_break_end,
            'break2_start' => $requestData->new_break2_start,
            'break2_end' => $requestData->new_break2_end,*/
            'remarks' => $requestData->remarks,
        ]);

        $attendance->breaks()->delete();

        foreach ($requestData->newBreaks as $newBreak) {
            $attendance->breaks()->create([
                'break_start' => $newBreak->new_break_in,
                'break_end' => $newBreak->new_break_out,
            ]);
        }
        /*if ($attendance->breaks->first())
        {
            $attendance->breaks->first()->update([
                'break_start' => $requestData->new_break_in,
                'break_end' => $requestData->new_break_out,
            ]);
        }*/

        $requestData->update([
            'approval_status' => '承認済み'
        ]);

        return redirect()
        ->back();
    }
}
