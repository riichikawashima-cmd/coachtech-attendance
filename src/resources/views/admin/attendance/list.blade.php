@extends('layouts.app')

@section('title', '勤怠一覧')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin_attendance_list.css') }}">
@endsection

@section('content')
<div class="attendance-list">
    <h2 class="attendance-list__title">
        {{ \Carbon\Carbon::parse($date)->format('Y年n月j日') }}の勤怠
    </h2>

    <div class="attendance-list__nav">
        <a href="{{ url('/admin/attendance/list?date=' . \Carbon\Carbon::parse($date)->subDay()->toDateString()) }}">← 前日</a>
        <span class="attendance-date">
            <span class="calendar-icon"></span>
            {{ \Carbon\Carbon::parse($date)->format('Y/m/d') }}
        </span>
        <a href="{{ url('/admin/attendance/list?date=' . \Carbon\Carbon::parse($date)->addDay()->toDateString()) }}">翌日 →</a>
    </div>

    <div class="attendance-table-wrapper">
        <table class="attendance-table">
            <thead>
                <tr>
                    <th>名前</th>
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
                    <td>{{ $attendance->user->name }}</td>

                    <td>
                        {{ $attendance->clock_in ? \Carbon\Carbon::parse($attendance->clock_in)->format('H:i') : '' }}
                    </td>

                    <td>
                        {{ $attendance->clock_out ? \Carbon\Carbon::parse($attendance->clock_out)->format('H:i') : '' }}
                    </td>

                    <td>
                        @php
                        $breakSeconds = $attendance->breaks->sum(function ($break) {
                        return strtotime($break->break_end) - strtotime($break->break_start);
                        });
                        @endphp
                        {{ gmdate('H:i', $breakSeconds) }}
                    </td>

                    <td>
                        @php
                        $workSeconds = ($attendance->clock_in && $attendance->clock_out)
                        ? strtotime($attendance->clock_out) - strtotime($attendance->clock_in) - $breakSeconds
                        : 0;
                        @endphp
                        {{ gmdate('H:i', $workSeconds) }}
                    </td>

                    <td>
                        <a href="/admin/attendance/{{ $attendance->id }}">詳細</a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" style="text-align:center; padding:20px; color:#666;">
                        データがありません
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- ページネーション --}}
    <div class="pagination">
        {{ $attendances->appends(['date' => $date])->links() }}
    </div>

</div>
@endsection