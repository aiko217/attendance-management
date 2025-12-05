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

$req = $latestRequest;

$get = function($requestValue, $original) use ($req) {
    if ($req && !empty($req->$requestValue)) {
        return Carbon::parse($req->$requestValue)->format('H:i');
    }
    return $original ? Carbon::parse($original)->format('H:i') : '';
};
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
                <span class="clock_in--span">{{ $get('new_clock_in', $attendance->clock_in) }}</span> ~ 
                <span class="clock_out--span">{{ $get('new_clock_out', $attendance->clock_out) }}</span>
            @else
            <input class="clock_in" type="time" name="clock_in" value="{{ old('clock_in', $get('new_clock_in', $attendance->clock_in)) }}">
             ~ 
            <input class="clock_out" type="time" name="clock_out" value="{{ old('clock_out', $get('new_clock_out', $attendance->clock_out)) }}">
            @error('clock_in')
                <div class="input-error">{{ $message }}</div>
            @enderror
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
                    <span class="break_start--span">{{ $get('new_break_in', $break1In) }}</span> ~
                    <span class="break_end--span">{{ $get('new_break_out', $break1Out) }}</span>
                    @endif
                @else
                    <input class="break_start" type="time" name="break_start" value="{{ old('break_start', $get('new_break_in', $break1In)) }}">
                    ~
                    <input class="break_end" type="time" name="break_end" value="{{ old('break_end', $get('new_break_out', $break1Out)) }}">
                    @error('break_start')
                    <div class="input-error">{{ $message }}</div>
                    @enderror
                    @error('break_end')
                    <div class="input-error">{{ $message }}</div>
                    @enderror
                @endif
            </td>
        </tr>
        <tr>
            <th>休憩2</th>
            <td>
                @if ($hasPending)
                    @if ($break2In || $break2Out)
                    <span class="break2_start--span">{{ $get('new_break2_in', $break2In) }}</span> ~
                    <span class="break2_end--span">{{ $get('new_break2_out', $break2Out) }}</span>
                    @endif
                @else
                    <input class="break2_start" type="time" name="break2_start" value="{{ old('break2_start', $get('new_break2_in', $break2In)) }}">
                    ~
                        <input class="break2_end" type="time" name="break2_end" value="{{ old('break2_end', $get('new_break2_out', $break2Out)) }}">
                        @error('break2_start')
                        <div class="input-error">{{ $message }}</div>
                        @enderror
                        @error('break2_end')
                        <div class="input-error">{{ $message }}</div>
                        @enderror
                @endif
            </td>
        </tr>
        <tr>
            <th>備考</th>
            <td>
            @if ($hasPending)
            <p class="remarks">{{ $req->remarks ?? $attendance->remarks }}</p>
            @else
                <textarea class="remarks" name="remarks" rows="3" cols="40"> {{ old('remarks', $attendance->remarks) }}</textarea>
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