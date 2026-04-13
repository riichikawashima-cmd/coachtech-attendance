@extends('layouts.app')

@section('title', '勤怠一覧')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance_list.css') }}">
@endsection

@section('content')
<div class="attendance-list">
    <div class="attendance-list__inner">

        <h1 class="attendance-list__heading">
            <span class="attendance-list__bar"></span>
            勤怠一覧
        </h1>

        <div class="attendance-list__month-nav">
            <a class="month-btn" href="{{ url('/attendance/list') }}?month={{ $prevMonth }}">
                ← 前月
            </a>

            <div class="month-label">
                <img src="{{ asset('images/calender.png') }}" alt="calendar" class="month-label__icon">
                <span class="month-label__text">{{ $monthLabel }}</span>
            </div>

            <a class="month-btn" href="{{ url('/attendance/list') }}?month={{ $nextMonth }}">
                翌月 →
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
                    @foreach ($dates as $day)
                    @php
                    $dateKey = $day->toDateString();
                    $attendance = $attendancesByDate->get($dateKey);

                    $clockInText = $attendance && $attendance->clock_in
                    ? \Carbon\Carbon::parse($attendance->clock_in)->format('H:i')
                    : '';

                    $clockOutText = $attendance && $attendance->clock_out
                    ? \Carbon\Carbon::parse($attendance->clock_out)->format('H:i')
                    : '';

                    $breakSeconds = 0;

                    if ($attendance) {
                    foreach ($attendance->breaks as $b) {
                    if ($b->break_start && $b->break_end) {
                    $breakSeconds += \Carbon\Carbon::parse($b->break_start)
                    ->diffInSeconds(\Carbon\Carbon::parse($b->break_end));
                    }
                    }
                    }

                    $breakMinutes = intdiv($breakSeconds, 60);
                    $bh = intdiv($breakMinutes, 60);
                    $bm = $breakMinutes % 60;

                    $hasWorkTime = $attendance && ($attendance->clock_in || $attendance->clock_out);

                    $breakText = $hasWorkTime
                    ? sprintf('%d:%02d', $bh, $bm)
                    : '';

                    $totalText = '';
                    if ($attendance && $attendance->clock_in && $attendance->clock_out) {
                    $workSeconds = \Carbon\Carbon::parse($attendance->clock_in)
                    ->diffInSeconds(\Carbon\Carbon::parse($attendance->clock_out));

                    $netSeconds = max(0, $workSeconds - $breakSeconds);
                    $netMinutes = intdiv($netSeconds, 60);

                    $th = intdiv($netMinutes, 60);
                    $tm = $netMinutes % 60;

                    $totalText = sprintf('%d:%02d', $th, $tm);
                    }
                    @endphp

                    <tr>
                        <td>{{ $day->isoFormat('MM/DD(ddd)') }}</td>
                        <td>{{ $clockInText }}</td>
                        <td>{{ $clockOutText }}</td>
                        <td>{{ $breakText }}</td>
                        <td>{{ $totalText }}</td>
                        <td class="detail">
                            <a href="{{ route('attendance.detail', ['date' => $dateKey]) }}">詳細</a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

    </div>
</div>
@endsection