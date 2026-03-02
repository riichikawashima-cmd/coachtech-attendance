@extends('layouts.app')

@section('title', '勤怠一覧')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance_list.css') }}">
@endsection

@section('content')
<div class="attendance-list">
    <div class="attendance-list__inner">

        <h2 class="attendance-list__heading">
            <span class="attendance-list__bar"></span>
            勤怠一覧
        </h2>

        <div class="attendance-list__month-nav">
            <a class="month-btn" href="{{ url('/attendance/list') }}?month={{ $prevMonth }}">
                &lt; 前月
            </a>

            <div class="month-label">
                {{ $monthLabel }}
            </div>

            <a class="month-btn" href="{{ url('/attendance/list') }}?month={{ $nextMonth }}">
                翌月 &gt;
            </a>
        </div>

        <div class="attendance-list__table-wrapper">
            <table class="attendance-list__table">
                <thead>
                    <tr>
                        <th>日付</th>
                        <th>出勤</th>
                        <th>退勤</th>
                        <th>休憩</th>
                        <th>合計</th>
                        <th>詳細</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($attendances as $attendance)
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($attendance->date)->isoFormat('MM/DD(ddd)') }}</td>

                        <td>
                            {{ $attendance->clock_in
                                    ? \Carbon\Carbon::parse($attendance->clock_in)->format('H:i')
                                    : '' }}
                        </td>

                        <td>
                            {{ $attendance->clock_out
                                    ? \Carbon\Carbon::parse($attendance->clock_out)->format('H:i')
                                    : '' }}
                        </td>

                        @php
                        // 休憩は「秒」で合算して、最後に分へ（端数ズレ防止）
                        $breakSeconds = 0;

                        foreach ($attendance->breaks as $b) {
                        if ($b->break_start && $b->break_end) {
                        $breakSeconds += \Carbon\Carbon::parse($b->break_start)
                        ->diffInSeconds(\Carbon\Carbon::parse($b->break_end));
                        }
                        }

                        $breakMinutes = intdiv($breakSeconds, 60);
                        $bh = intdiv($breakMinutes, 60);
                        $bm = $breakMinutes % 60;
                        @endphp

                        {{-- 休憩時間合計 --}}
                        <td>{{ sprintf('%d:%02d', $bh, $bm) }}</td>

                        {{-- 合計（勤務時間 - 休憩） --}}
                        <td>
                            @php
                            $totalText = '';

                            if ($attendance->clock_in && $attendance->clock_out) {
                            $workSeconds = \Carbon\Carbon::parse($attendance->clock_in)
                            ->diffInSeconds(\Carbon\Carbon::parse($attendance->clock_out));

                            $netSeconds = max(0, $workSeconds - $breakSeconds);
                            $netMinutes = intdiv($netSeconds, 60);

                            $th = intdiv($netMinutes, 60);
                            $tm = $netMinutes % 60;

                            $totalText = sprintf('%d:%02d', $th, $tm);
                            }
                            @endphp

                            {{ $totalText }}
                        </td>

                        <td>詳細</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" style="text-align:center; padding: 18px 0;">
                            データがありません
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

    </div>
</div>
@endsection