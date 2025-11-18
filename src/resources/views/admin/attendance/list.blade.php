@extends('layouts.admin_app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/attendance/list.css') }}">
@endsection

@php
use Carbon\Carbon;
@endphp

@section('content')
<div class="attendance-list">
    <h2>{{ $date->format('Y年m月d日') }}の勤怠</h2>

    <div class="date-nav">
        <div class="date-left">
    <img src="{{ asset('images/arrow.png') }}" alt="アプリロゴ" class="arrow-logo" />
        <a href="{{ route('admin.index', ['date' => $date->copy()->subDay()->toDateString()]) }}" class="date-btn prev">前日</a>
        </div>
        <div class="date-center">
        <img src="{{ asset('images/calendar.png') }}" alt="アプリロゴ" class="calendar-logo" />
        <p class="current-date">{{ $date->format('Y/m/d') }}</p>
        </div>
        <div class="date-right">
        <a href="{{ route('admin.index', ['date' => $date->copy()->addDay()->toDateString()]) }}" class="date-btn next">翌日
        <img src="{{ asset('images/arrow.png') }}" alt="アプリロゴ" class="arrow-logo flip" />
        </a>
        </div>
    </div>
    <table class="attendance-table">
        <thead>
        <tr>
            <th class="date">名前</th>
            <th class="attendance">出勤</th>
            <th class="leaving">退勤</th>
            <th class="break">休憩</th>
            <th class="total">合計</th>
            <th class="detail">詳細</th>
        </tr>
        </thead>
        <tbody>
            @foreach ($attendances as $attendance)   
            <tr>
                <td>{{ $attendance->user->name }}</td>
                <td>{{ optional($attendance)->clock_in ? Carbon::parse($attendance->clock_in)->format('H:i') : '' }}</td>
                <td>{{ optional($attendance)->clock_out ? Carbon::parse($attendance->clock_out)->format('H:i') : '' }}</td>
                <td>{{ optional($attendance)->total_break_time ? Carbon::parse($attendance->total_break_time)->format('G:i') : '' }}</td>
                <td>{{ optional($attendance)->work_time ? Carbon::parse($attendance->work_time)->format('G:i') : '' }}</td>
                <td>
                    <a href="{{ route('admin.show', $attendance->id ) }}" class="detail-btn">詳細</a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection