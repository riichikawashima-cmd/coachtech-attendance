@extends('layouts.app')

@section('title', '勤怠詳細')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance_detail.css') }}">
@endsection

@section('content')
<div class="attendance-detail">
    <div class="attendance-detail__inner">

        <h2 class="attendance-detail__heading">
            <span class="attendance-detail__bar"></span>
            勤怠詳細
        </h2>

        <div class="attendance-detail__card">

            @if ($errors->any())
            <div class="approval-message">
                @foreach ($errors->all() as $error)
                <div>{{ $error }}</div>
                @endforeach
            </div>
            @endif

            @if ($attendance && $attendance->requested_at)
            <div class="approval-message">
                ※承認待ちのため修正はできません※
            </div>
            @endif

            @php
            $isLocked = $attendance && $attendance->requested_at;

            $breaks = $attendance ? $attendance->breaks : collect();
            $break1 = $breaks->get(0);
            $break2 = $breaks->get(1);
            @endphp

            <form method="POST" action="{{ route('attendance.apply', ['date' => $day->toDateString()]) }}">
                @csrf

                <table class="detail-table">
                    <tr>
                        <th>名前</th>
                        <td class="detail-table__name" colspan="3">
                            {{ Auth::user()->name }}
                        </td>
                    </tr>

                    <tr>
                        <th>日付</th>
                        <td class="detail-table__date">{{ $day->isoFormat('YYYY年') }}</td>
                        <td class="detail-table__sep"></td>
                        <td class="detail-table__date">{{ $day->isoFormat('M月D日') }}</td>
                    </tr>

                    <tr>
                        <th>出勤・退勤</th>
                        <td class="detail-table__time">
                            <input type="time" class="time-box" name="clock_in"
                                value="{{ $attendance && $attendance->clock_in ? \Carbon\Carbon::parse($attendance->clock_in)->format('H:i') : '' }}"
                                @if($isLocked) disabled @endif>
                        </td>
                        <td class="detail-table__sep">〜</td>
                        <td class="detail-table__time">
                            <input type="time" class="time-box" name="clock_out"
                                value="{{ $attendance && $attendance->clock_out ? \Carbon\Carbon::parse($attendance->clock_out)->format('H:i') : '' }}"
                                @if($isLocked) disabled @endif>
                        </td>
                    </tr>

                    <tr>
                        <th>休憩</th>
                        <td class="detail-table__time">
                            <input type="time" class="time-box" name="break1_start"
                                value="{{ $break1 && $break1->break_start ? \Carbon\Carbon::parse($break1->break_start)->format('H:i') : '' }}"
                                @if($isLocked) disabled @endif>
                        </td>
                        <td class="detail-table__sep">〜</td>
                        <td class="detail-table__time">
                            <input type="time" class="time-box" name="break1_end"
                                value="{{ $break1 && $break1->break_end ? \Carbon\Carbon::parse($break1->break_end)->format('H:i') : '' }}"
                                @if($isLocked) disabled @endif>
                        </td>
                    </tr>

                    <tr>
                        <th>休憩2</th>
                        <td class="detail-table__time">
                            <input type="time" class="time-box" name="break2_start"
                                value="{{ $break2 && $break2->break_start ? \Carbon\Carbon::parse($break2->break_start)->format('H:i') : '' }}"
                                @if($isLocked) disabled @endif>
                        </td>
                        <td class="detail-table__sep">〜</td>
                        <td class="detail-table__time">
                            <input type="time" class="time-box" name="break2_end"
                                value="{{ $break2 && $break2->break_end ? \Carbon\Carbon::parse($break2->break_end)->format('H:i') : '' }}"
                                @if($isLocked) disabled @endif>
                        </td>
                    </tr>

                    <tr>
                        <th>備考</th>
                        <td colspan="3">
                            <textarea class="remark-box" name="remark" @if($isLocked) disabled @endif>{{ $attendance->remark ?? '' }}</textarea>
                        </td>
                    </tr>
                </table>

                <div class="attendance-detail__actions">
                    @if ($attendance && !$attendance->requested_at)
                    <button type="submit" class="edit-btn">申請</button>
                    @endif
                </div>

            </form>

        </div>

    </div>
</div>
@endsection