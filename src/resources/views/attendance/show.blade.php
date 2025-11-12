@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance/show.css') }}">
@endsection

@php
use Carbon\Carbon;
$break1 = $attendance->breaks[0] ?? null;
$break2 = $attendance->breaks[1] ?? null;
@endphp

@section('content')
<div class="attendance-detail">
    <h2>勤怠詳細</h2>
@if ($errors->any())
<div class="error-messages">
    <ul>
        @foreach ($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

@if (session('success'))
<div class="success-message">
    {{ session('success') }}
</div>
@endif

    <form action="{{ route('attendance.update', $attendance->id) }}" method="POST">
    @csrf
    @method('PUT')

    <table class="attendance-table">
        <tr>
            <th>名前</th>
            <td>
                <span class="name">{{ $attendance->user->name ?? '' }}
                </span>
            </td>
        </tr>
        <tr>
            <th>日付</th>
            <td>
                <span class="year">{{ $year }}年</span>
                <span class="date">{{ $date->format('m月d日') }}</span>
            </td>
        </tr>
        <tr>
            <th>出勤・退勤</th>
            <td>
            @if ($hasPending)
                <span class="clock_in">{{ old('clock_in', optional($attendance)->clock_in ?  Carbon::parse($attendance->clock_in)->format('H:i') : '') }}</span> ~ 
                <span class="clock_out">{{ old('clock_out', optional($attendance)->clock_out ?  Carbon::parse($attendance->clock_out)->format('H:i') : '') }}</span>
            @else
            <input class="clock_in" type="time" name="clock_in" value="{{ old('clock_in', optional($attendance)->clock_in ? Carbon::parse($attendance->clock_in)->format('H:i') : '') }}"> ~ 
            <input class="clock_out" type="time" name="clock_out" value="{{ old('clock_out', optional($attendance)->clock_out ? Carbon::parse($attendance->clock_out)->format('H:i') : '') }}">
            @endif
            </td>
        </tr>
        <tr>
            <th>休憩</th>
            <td>
            @if ($hasPending)
            <span class="break_start">
                {{ old('break_start', isset($break1) && $break1->break_start ? Carbon::parse($break1->break_start)->format('H:i') : '') }}</span> ~ 
            <span class="break_end">
                {{ old('break_end', isset($break1) && $break1->break_end ? Carbon::parse($break1->break_end)->format('H:i') : '') }}</span>
            @else
            <input class="break_start" type="time" name="break_start" value="{{ old('break_start', isset($break1) && $break1->break_start ? Carbon::parse($break1->break_start)->format('H:i') : '') }}"> ~ 
            <input class="break_end" type="time" name="break_end" value="{{ old('break_end', isset($break1) && $break1->break_end ? Carbon::parse($break1->break_end)->format('H:i') : '') }}">
            @endif
            </td>
        </tr>
        @php
            $hasOld = !empty(old()); 
        @endphp

        @if (
            !$hasOld ||
            ($hasOld && (old('break2_start') || old('break2_end')))
            )
        <tr>
            <th>休憩2</th>
            <td>
            @if ($hasPending)
            <span class="break2_start">
                {{ old('break2_start', isset($break2) && $break2->break_start ? Carbon::parse($break2->break_start)->format('H:i') : '') }}</span> ~ 
            <span class="break2_end">{{ old('break2_end', isset($break2) && $break2->break_end ? Carbon::parse($break2->break_end)->format('H:i') : '') }}</span>
            @else
            <input class="break2_start" type="time" name="break2_start" value="{{ old('break2_start', isset($break2) && $break2->break_start ? Carbon::parse($break2->break_start)->format('H:i') : '') }}"> ~
            <input class="break2_end" type="time" name="break2_end" value="{{ old('break2_end', isset($break2) && $break2->break_end ? Carbon::parse($break2->break_end)->format('H:i') : '') }}">
            @endif
            </td>
        </tr>
        @endif
        <tr>
            <th>備考</th>
            <td>
            @if ($hasPending)
            <p class="remarks">{{ old('remarks', $attendance->remarks ?? '') }}</p>
            @else
                <textarea class="remarks" name="remarks" rows="3" cols="40"> {{ old('remarks', $attendance->remarks ?? '') }}</textarea>
            @endif
            </td>
        </tr>
    </table>
    <div class="btn-area">
        @if ($hasPending)
            <span class="pending-message">*承認待ちのため修正はできません。</span>
        @else
            <button type="submit" class="update">修正</button>
        @endif
    </div>
    </form>
</div>
@endsection