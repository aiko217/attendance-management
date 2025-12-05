<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\AttendanceRequest;

class AttendanceRequestController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        $status = $request->query('status', 'pending');

        if ($status === 'approved') {
            $requests = AttendanceRequest::where('user_id', $user->id)
            ->where('approval_status', '承認済み')
            ->orderByDesc('request_date')
            ->paginate(10);
        } else {
            $requests = AttendanceRequest::where('user_id', $user->id)
            ->where('approval_status', '承認待ち')
            ->orderByDesc('request_date')
            ->paginate(10);
        }
        return view('attendance_requests.list', compact('requests', 'status'));
    }
    /*public function detail($id)
    {
    $requestData = AttendanceRequest::with('attendance', 'user')
        ->findOrFail($id);

    $attendance = $requestData->attendance;

    $date = \Carbon\Carbon::parse($attendance->date);
    $year = $date->year;

    return view('admin.request.approve', [
        'requestData' => $requestData,
        'attendance'  => $attendance,
        'date'        => $date,
        'year'        => $year,
        'hasPending'  => false, 
    ]);
    }*/
}
