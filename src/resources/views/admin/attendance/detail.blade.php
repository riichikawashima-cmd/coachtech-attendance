@extends('layouts.app')

@section('title', '勤怠詳細')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin_attendance_detail.css') }}">
@endsection

@section('content')
<div class="attendance-detail">
    <div class="attendance-detail__inner">

        <h2 class="attendance-detail__heading">
            <span class="attendance-detail__bar"></span>
            勤怠詳細
        </h2>

        <form method="POST" action="/admin/attendance/{{ $attendance->id }}">
            @csrf

            <div class="attendance-detail__card">
                <table class="detail-table">
                    <tr>
                        <th>名前</th>
                        <td class="detail-table__name" colspan="3">
                            {{ $attendance->user->name }}
                        </td>
                    </tr>

                    <tr>
                        <th>日付</th>
                        <td class="detail-table__date">
                            {{ \Carbon\Carbon::parse($attendance->date)->format('Y年') }}
                        </td>
                        <td class="detail-table__sep"></td>
                        <td class="detail-table__date">
                            {{ \Carbon\Carbon::parse($attendance->date)->format('n月j日') }}
                        </td>
                    </tr>

                    <tr>
                        <th>出勤・退勤</th>
                        <td class="detail-table__time">
                            <input type="text" class="time-box" name="clock_in"
                                value="{{ $attendance->clock_in ? \Carbon\Carbon::parse($attendance->clock_in)->format('H:i') : '' }}">
                        </td>
                        <td class="detail-table__sep">〜</td>
                        <td class="detail-table__time">
                            <input type="text" class="time-box" name="clock_out"
                                value="{{ $attendance->clock_out ? \Carbon\Carbon::parse($attendance->clock_out)->format('H:i') : '' }}">
                        </td>
                    </tr>

                    @php
                    $break1 = $attendance->breaks->get(0);
                    $break2 = $attendance->breaks->get(1);
                    @endphp

                    <tr>
                        <th>休憩</th>
                        <td class="detail-table__time">
                            <input type="text" class="time-box" name="breaks[0][break_start]"
                                value="{{ $break1 && $break1->break_start ? \Carbon\Carbon::parse($break1->break_start)->format('H:i') : '' }}">
                        </td>
                        <td class="detail-table__sep">〜</td>
                        <td class="detail-table__time">
                            <input type="text" class="time-box" name="breaks[0][break_end]"
                                value="{{ $break1 && $break1->break_end ? \Carbon\Carbon::parse($break1->break_end)->format('H:i') : '' }}">
                        </td>
                    </tr>

                    <tr>
                        <th>休憩2</th>
                        <td class="detail-table__time">
                            <input type="text" class="time-box" name="breaks[1][break_start]"
                                value="{{ $break2 && $break2->break_start ? \Carbon\Carbon::parse($break2->break_start)->format('H:i') : '' }}">
                        </td>
                        <td class="detail-table__sep">〜</td>
                        <td class="detail-table__time">
                            <input type="text" class="time-box" name="breaks[1][break_end]"
                                value="{{ $break2 && $break2->break_end ? \Carbon\Carbon::parse($break2->break_end)->format('H:i') : '' }}">
                        </td>
                    </tr>

                    <tr>
                        <th>備考</th>
                        <td colspan="3">
                            <textarea class="remark-box" name="note">{{ $attendance->note ?? '' }}</textarea>
                        </td>
                    </tr>
                </table>
            </div>

            <div class="attendance-detail__actions">
                <button type="submit" class="edit-btn">修正</button>
            </div>
        </form>

    </div>
</div>
@endsection