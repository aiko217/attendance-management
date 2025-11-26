@extends('layouts.admin_app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/attendance/show.css') }}">
@endsection

@php
use Carbon\Carbon;

$break1In = $attendance->breaks[0]->break_start ?? null;
$break1Out = $attendance->breaks[0]->break_end ?? null;

$break2In = $attendance->breaks[1]->break_start ?? null;
$break2Out = $attendance->breaks[1]->break_end ?? null;
@endphp

@section('content')
<div class="attendance-detail">
    <h2>勤怠詳細</h2>

    
@if (session('success'))
<div class="success-message">
    {{ session('success') }}
</div>
@endif

    <form action="{{ route('admin.update', $attendance->id) }}" method="POST">
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
                <span class="clock_in--span">{{ old('clock_in', optional($attendance)->clock_in ?  Carbon::parse($attendance->clock_in)->format('H:i') : '') }}</span> ~ 
                <span class="clock_out--span">{{ old('clock_out', optional($attendance)->clock_out ?  Carbon::parse($attendance->clock_out)->format('H:i') : '') }}</span>
            @else
            <input class="clock_in" type="time" name="clock_in" value="{{ old('clock_in', optional($attendance)->clock_in ? Carbon::parse($attendance->clock_in)->format('H:i') : '') }}">
            @error('clock_in')
                <div class="input-error">{{ $message }}</div>
            @enderror ~ 
            <input class="clock_out" type="time" name="clock_out" value="{{ old('clock_out', optional($attendance)->clock_out ? Carbon::parse($attendance->clock_out)->format('H:i') : '') }}">
            @error('clock_out')
                <div class="input-error">{{ $message }}</div>
            @enderror
            @endif
            </td>
        </tr>
        <tr>
            <th>休憩</th>
            <td>
                @if ($hasPending)
                    @if ($break1In || $break1Out)
                        <span class="break_start--span">{{ $break1In ? Carbon::parse($break1In)->format('H:i') : '' }}</span> ~
                        <span class="break_end--span">{{ $break1Out ? Carbon::parse($break1Out)->format('H:i') : '' }}</span>
                    @endif
                @else
                        <input class="break_start" type="time" name="break_start" value="{{ $break1In ? Carbon::parse($break1In)->format('H:i') : '' }}">@error('break_start')
                        <div class="input-error">{{ $message }}</div>
                        @enderror ~
                        <input class="break_end" type="time" name="break_end" value="{{ $break1Out ? Carbon::parse($break1Out)->format('H:i') : '' }}">
                        @error('break_end')
                        <div class="input-error">{{ $message }}</div>
                        @enderror
                @endif
            </td>
        </tr>
        @if (!$hasPending || ($break2In || $break2Out))
        <tr>
            <th>休憩2</th>
            <td>
                @if ($hasPending)
                        <span class="break2_start--span">{{ $break2In ? Carbon::parse($break2In)->format('H:i') : '' }}</span> ~
                        <span class="break2_end--span">{{ $break2Out ? Carbon::parse($break2Out)->format('H:i') : '' }}</span>
                @else
                        <input class="break2_start" type="time" name="break2_start" value="{{ $break2In ? Carbon::parse($break2In)->format('H:i') : '' }}">
                        @error('break2_start')
                        <div class="input-error">{{ $message }}</div>
                        @enderror ~
                        <input class="break2_end" type="time" name="break2_end" value="{{ $break2Out ? Carbon::parse($break2Out)->format('H:i') : '' }}">
                        @error('break2_end')
                        <div class="input-error">{{ $message }}</div>
                        @enderror
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
                @error('remarks')
                <div class="input-error">{{ $message }}</div>
                @enderror
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