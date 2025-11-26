<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
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
}
