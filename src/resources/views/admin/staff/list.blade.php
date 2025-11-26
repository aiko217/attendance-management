@extends('layouts.admin_app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/staff/list.css') }}">
@endsection

@section('content')
<div class="staff-list">
    <h2>スタッフ一覧</h2>
   
    <table class="staff-table">
        <thead>
            <tr>
                <th>名前</th>
                <th>メールアドレス</th>
                <th>月次勤怠</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($users as $user)
            <tr>
                <td>{{ $user->name }}</td>
                <td>{{ $user->email }}</td>
                <td>
                    <a href="{{ route('admin.staff.attendance_list', $user->id) }}" class="detail_btn">詳細</a>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="3">スタッフが存在しません</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection