@extends('layouts.admin_app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/request/approve.css') }}">
@endsection

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
                <span class="clock_in">{{ $formattedClockIn }}</span> ~ 
                <span class="clock_out">{{ $formattedClockOut }}</span>
            
            </td>
        </tr>
        @php
            $break1 = $referBreaks[0] ?? null;
            $break2 = $referBreaks[1] ?? null;

            $format = function ($time) {
            return $time ? \Carbon\Carbon::parse($time)->format('H:i') : '';
            };
        @endphp
        <tr>
            <th>休憩</th>
            <td>
                <span class="break_start">{{ $format($break1->new_break_in ?? $break1->break_start ?? null) }}
                </span>
                〜
                <span class="break_end">{{ $format($break1->new_break_out ?? $break1->break_end ?? null) }}
                </span>
            </td>
        </tr>
        <tr>
            <th>休憩2</th>
            <td>
                <span class="break_start">{{ $format($break2->new_break_in ?? $break2->break_start ?? null) }}
                </span>
                〜
                <span class="break_end">{{ $format($break2->new_break_out ?? $break2->break_end ?? null) }}
                </span>
            </td>
        </tr>    
        <tr>
            <th>備考</th>
            <td>
                <p class="remarks">{{ $formattedRemarks }}</p>
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