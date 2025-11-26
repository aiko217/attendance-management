@extends('layouts.admin_app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/request/list.css') }}">
@endsection

@section('content')
<div class="attendance-requests">
    <h2>申請一覧</h2>
    
    <div class="border">
        <ul class="border__list">
            <li><a href="{{ route('admin.stamp_correction_request.list', ['status' => 'pending']) }}"
           class="tab {{ $status === 'pending' ? 'active' : '' }}">承認待ち</a></li>
            <li><a href="{{ route('admin.stamp_correction_request.list', ['status' => 'approved']) }}"
           class="tab {{ $status === 'approved' ? 'active' : '' }}">承認済み</a></li>
        </ul>
    </div>
    <table class="request-table">
        <thead>
            <tr>
                <th>状態</th>
                <th>名前</th>
                <th>対象日時</th>
                <th>申請理由</th>
                <th>申請日時</th>
                <th>詳細</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($requests as $request)
            <tr>
                <td>{{ $request->approval_status }}</td>
                <td>{{ $request->user->name ?? '' }}</td>
                <td>{{ \Carbon\Carbon::parse($request->new_date)->format('Y/m/d') }}</td>
                <td>{{ $request->remarks ?? '' }}</td>
                <td>{{ \Carbon\Carbon::parse($request->request_date)->format('Y/m/d') }}</td>
                <td>
                    <a href="{{ route('admin.stamp_correction_request.approve', $request->attendance_id) }}" class="detail_btn">詳細</a>
                </td>
            </tr>
            @empty
                <tr>
                    <td colspan="6" class="no-data">申請はありません。</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="pagination">
        {{ $requests->links() }}
    </div>
</div>
@endsection