@extends('layouts.app')

@section('title', '修正申請承認')

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

        @php
        $attendance = $request->attendance;
        $day = \Carbon\Carbon::parse($attendance->date)->locale('ja');
        $isApproved = $request->status === 'approved';
        $requestBreaks = $request->breaks ?? collect();
        @endphp

        <form method="POST" action="{{ route('admin.stamp_correction_request.approve', $request->id) }}">
            @csrf

            <div class="attendance-detail__card">
                <table class="detail-table">
                    <tr>
                        <th>名前</th>
                        <td class="detail-table__name" colspan="3">
                            {{ $request->user->name }}
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
                            <input type="text" class="time-box" name="clock_in"
                                value="{{ $request->requested_clock_in ? \Carbon\Carbon::parse($request->requested_clock_in)->format('H:i') : '' }}"
                                readonly>
                        </td>
                        <td class="detail-table__sep">〜</td>
                        <td class="detail-table__time">
                            <input type="text" class="time-box" name="clock_out"
                                value="{{ $request->requested_clock_out ? \Carbon\Carbon::parse($request->requested_clock_out)->format('H:i') : '' }}"
                                readonly>
                        </td>
                    </tr>

                    @foreach ($requestBreaks as $index => $break)
                    <tr>
                        <th>休憩{{ $index + 1 }}</th>
                        <td class="detail-table__time">
                            <input type="text" class="time-box" name="break{{ $index + 1 }}_start"
                                value="{{ $break->break_start ? \Carbon\Carbon::parse($break->break_start)->format('H:i') : '' }}"
                                readonly>
                        </td>
                        <td class="detail-table__sep">〜</td>
                        <td class="detail-table__time">
                            <input type="text" class="time-box" name="break{{ $index + 1 }}_end"
                                value="{{ $break->break_end ? \Carbon\Carbon::parse($break->break_end)->format('H:i') : '' }}"
                                readonly>
                        </td>
                    </tr>
                    @endforeach

                    <tr>
                        <th>備考</th>
                        <td colspan="3">
                            <textarea class="remark-box" name="remark" readonly>{{ $request->requested_note ?? '' }}</textarea>
                        </td>
                    </tr>
                </table>
            </div>

            <div class="attendance-detail__actions">
                @if (!$isApproved)
                <button type="submit" class="edit-btn">承認</button>
                @else
                <button type="button" class="edit-btn" disabled>承認済み</button>
                @endif
            </div>
        </form>

    </div>
</div>
@endsection