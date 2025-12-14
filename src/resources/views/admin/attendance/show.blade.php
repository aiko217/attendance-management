@extends('layouts.admin_app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/attendance/show.css') }}">
@endsection

@php
use Carbon\Carbon;
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

                <span class="clock_in--span">{{ $referClockIn ?  Carbon::parse($referClockIn)->format('H:i') : '' }}</span> ~ 
                <span class="clock_out--span">{{ $referClockOut ? Carbon::parse($referClockOut)->format('H:i') : '' }}</span>
            @else
            <input class="clock_in" type="time" name="clock_in" value="{{ old('clock_in', $referClockIn ? Carbon::parse($referClockIn)->format('H:i') : '') }}">
             ~ 
            <input class="clock_out" type="time" name="clock_out" value="{{ old('clock_out', $referClockOut ? Carbon::parse($referClockOut)->format('H:i') : '') }}">
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
            @php $break1 = $referBreaks[0] ?? null; @endphp

            @if($hasPending)
                @if ($break1)    
                
                <span class="break_start--span">{{ Carbon::parse($break1->start)->format('H:i') }}</span>
                〜
                <span class="break_end--span">{{ Carbon::parse($break1->end)->format('H:i') }}</span>
                @else
                    
                @endif
            @else
                <input class="break_start" type="time" name="new_breaks[0][in]"
                value="{{ $break1 ? Carbon::parse($break1->start)->format('H:i') : '' }}">

                〜
                <input class="break_end" type="time" name="new_breaks[0][out]"
                value="{{ $break1 ? Carbon::parse($break1->end)->format('H:i') : '' }}">
            @error('new_breaks.0.in')
                <div class="input-error">{{ $message }}</div>
            @enderror
            @error('new_breaks.0.out')
                <div class="input-error">{{ $message }}</div>
            @enderror
            @endif
            </td>
        </tr>
        @if (!$hasPending)
        <tr>
            <th>休憩2</th>
            <td>
            @php
                $break2 = $referBreaks[1] ?? null;
            @endphp

                <input class="break_start" type="time" name="new_breaks[1][in]"
                value="{{ $break2 ? Carbon::parse($break2->start)->format('H:i') : '' }}">
                〜
                <input class="break_end" type="time" name="new_breaks[1][out]"
                value="{{ $break2 ? Carbon::parse($break2->end)->format('H:i') : '' }}">
            @error('new_breaks.1.in') 
                <div class="input-error">{{ $message }}</div> 
            @enderror
            @error('new_breaks.1.out') 
                <div class="input-error">{{ $message }}</div> 
            @enderror
            </td>
        </tr>
        @endif
        @if ($hasPending)
        <tr>
            <th>休憩２</th>
            <td>
                @if($referBreaks->count() > 1)
                    @foreach ($referBreaks as $i => $break)
                        @if ($i > 0)
                            <span class="break2_start--span">{{ Carbon::parse($break->start)->format('H:i') }}</span>
                            〜
                            <span class="break2_end--span">{{ Carbon::parse($break->end)->format('H:i') }}</span>
                        @endif
                    @endforeach
                @else
                    
                @endif
            </td>
        </tr>
        @endif
        <tr>
            <th>備考</th>
            <td>
            @if ($hasPending)
                <p class="remarks">{{ old('remarks', $referRemarks) }}</p>
            @else
                <textarea class="remarks" name="remarks" rows="3" cols="40"> {{ old('remarks', $referRemarks) }}</textarea>
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