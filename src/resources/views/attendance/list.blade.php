@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance/list.css') }}">
@endsection

@php
use Carbon\Carbon;
@endphp

@section('content')
<div class="attendance-list">
    <h2>勤怠一覧</h2>

    <div class="month-nav">
        <div class="month-left">
    <img src="{{ asset('images/arrow.png') }}" alt="アプリロゴ" class="arrow-logo" />
        <a href="{{ route('attendance.list', ['year' => Carbon::create($year, $month)->subMonth()->year, 'month' => Carbon::create($year, $month)->subMonth()->month]) }}" class="month-btn prev">前月</a>
        </div>
        <div class="month-center">
        <img src="{{ asset('images/calendar.png') }}" alt="アプリロゴ" class="calendar-logo" />
        <p class="current-month">{{ $year }}年{{ $month }}月</p>
        </div>
        <div class="month-right">
        <a href="{{ route('attendance.list', ['year' => Carbon::create($year, $month)->addMonth()->year, 'month' => Carbon::create($year, $month)->addMonth()->month]) }}" class="month-btn next">翌月
        <img src="{{ asset('images/arrow.png') }}" alt="アプリロゴ" class="arrow-logo flip" />
        </a>
        </div>
    </div>
    <table class="attendance-table">
        <thead>
        <tr>
            <th class="date">日付</th>
            <th class="attendance">出勤</th>
            <th class="leaving">退勤</th>
            <th class="break">休憩</th>
            <th class="total">合計</th>
            <th class="detail">詳細</th>
        </tr>
        </thead>
        <tbody>
            @foreach ($dates as $date)
            @php
            $attendance = $attendances->firstWhere('date', $date->toDateString());
            @endphp
            <tr>
                <td>{{ $date->format('m/d') }}({{ ['日','月','火','水','木','金','土'][$date->dayOfWeek] }}) </td>
                <td>{{ optional($attendance)->clock_in ? Carbon::parse($attendance->clock_in)->format('H:i') : '' }}</td>
                <td>{{ optional($attendance)->clock_out ? Carbon::parse($attendance->clock_out)->format('H:i') : '' }}</td>
                <td>{{ optional($attendance)->total_break_time ? Carbon::parse($attendance->total_break_time)->format('G:i') : '' }}</td>
                <td>{{ optional($attendance)->work_time ? Carbon::parse($attendance->work_time)->format('G:i') : '' }}</td>
                <td>
                    @if ($attendance)
                    <a href="{{ route('attendance.show', $attendance->id ) }}" class="detail-btn">詳細</a>
                    @else

                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection