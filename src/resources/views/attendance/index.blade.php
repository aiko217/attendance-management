@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance/index.css') }}">
@endsection

@section('content')
<div class="attendance">
        <strong>{{ $attendance->attendance_status ?? '勤務外' }}</strong>

    @if (session('message'))
        <p class="message">{{ session('message') }}</p>
    @endif

    <div class="buttons">
        @if (empty($attendance) || $attendance->attendance_status === '勤務外')
            <form action="{{ route('attendance.clockIn') }}" method="POST">
                @csrf
            <p class="date">{{ now()->format('Y年m月d日 ') }}（{{ ['日', '月', '火', '水', '木', '金', '土'][now()->dayOfWeek] }}）</p>
            <p class="time">{{ now()->format('H:i') }}</p>
                <button type="submit" class="btn btn-black">出勤</button>
            </form>

        @elseif ($attendance->attendance_status === '出勤中')
        <p class="date">{{ now()->format('Y年m月d日 ') }}（{{ ['日', '月', '火', '水', '木', '金', '土'][now()->dayOfWeek] }}）</p>
        <p class="time">{{ now()->format('H:i') }}</p>
        <div class="button-row">
            <form action="{{ route('attendance.clockOut') }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-black">退勤</button>
            </form>
            <form action="{{ route('attendance.breakStart') }}" method="POST">
                @csrf
               
                <button type="submit" class="btn btn-white">休憩入</button>
            </form>
            
        @elseif ($attendance->attendance_status === '休憩中')
            <form action="{{ route('attendance.breakEnd') }}" method="POST">
                @csrf
                <p class="date">{{ now()->format('Y年m月d日 ') }}（{{ ['日', '月', '火', '水', '木', '金', '土'][now()->dayOfWeek] }}）</p>
            <p class="time">{{ now()->format('H:i') }}</p>
                <button type="submit" class="btn btn-white">休憩戻</button>
            </form>

        @elseif ($attendance->attendance_status === '退勤済')
        <p class="date">{{ now()->format('Y年m月d日 ') }}（{{ ['日', '月', '火', '水', '木', '金', '土'][now()->dayOfWeek] }}）</p>
            <p class="time">{{ now()->format('H:i') }}</p>
            <p class="end">お疲れ様でした。</p>
        @endif
    </div>
</div>
@endsection
