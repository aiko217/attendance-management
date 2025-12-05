@extends('layouts.admin_app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/request/approve.css') }}">
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
                <span class="clock_in">{{ optional($attendance)->clock_in ?  Carbon::parse($attendance->clock_in)->format('H:i') : '' }}</span> ~ 
                <span class="clock_out">{{ optional($attendance)->clock_out ?  Carbon::parse($attendance->clock_out)->format('H:i') : '' }}</span>
            
            </td>
        </tr>
        <tr>
            <th>休憩</th>
            <td>
                    <span class="break_start">{{ $break1In ? Carbon::parse($break1In)->format('H:i') : '' }}</span> ~
                    <span class="break_end">{{ $break1Out ? Carbon::parse($break1Out)->format('H:i') : '' }}</span>
            </td>
        </tr>
        <tr>
            <th>休憩2</th>
            <td>
                    <span class="break2_start">{{ $break2In ? Carbon::parse($break2In)->format('H:i') : '' }}</span> 
                    <span class="break2_end">{{ $break2Out ? Carbon::parse($break2Out)->format('H:i') : '' }}</span> 
            </td>
        </tr>    
        <tr>
            <th>備考</th>
            <td>
            <p class="remarks">{{  $attendance->remarks ?? '' }}</p>
            </td>
        </tr>
    </table>
    <div class="btn-area">
        @if ($requestData->approval_status === '承認待ち')
        <form action="{{ route('admin.stamp_correction_request.approve', $requestData->id) }}" method="POST">
            @csrf
            <button type="submit" class="approve-btn">承認</button>    
        </form>
          @else
          <button type="button" class="approved-btn" disabled>承認済み</button>
        @endif
    </div>
</div>
@endsection