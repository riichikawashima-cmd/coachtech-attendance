@extends('layouts.app')

@section('title', '修正申請承認')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance_detail.css') }}">
@endsection

@section('content')
<div class="attendance-detail">
    <div class="attendance-detail__inner">

        <h1 class="attendance-detail__heading">
            <span class="attendance-detail__bar"></span>
            勤怠詳細
        </h1>

        @php
        $attendance = $request->attendance;
        $day = \Carbon\Carbon::parse($attendance->date)->locale('ja');
        $isApproved = $request->status === 'approved';
        $requestBreaks = $request->breaks ?? collect();
        @endphp

        <div class="attendance-detail__card">
            <table class="detail-table">
                <tr>
                    <th>名前</th>
                    <td class="detail-table__value" colspan="3">
                        <div class="detail-table__name">
                            {{ $request->user->name }}
                        </div>
                    </td>
                </tr>

                <tr>
                    <th>日付</th>
                    <td class="detail-table__value" colspan="3">
                        <div class="detail-table__date-row">
                            <span class="detail-table__date-item">{{ $day->isoFormat('YYYY年') }}</span>
                            <span class="detail-table__date-item">{{ $day->isoFormat('M月D日') }}</span>
                        </div>
                    </td>
                </tr>

                <tr class="detail-table__time-row">
                    <th>出勤・退勤</th>
                    <td class="detail-table__value" colspan="3">
                        <div class="detail-table__time-row-inner">
                            <div class="detail-table__time-box-wrap">
                                <span class="detail-table__text">
                                    {{ $request->requested_clock_in ? \Carbon\Carbon::parse($request->requested_clock_in)->format('H:i') : '' }}
                                </span>
                            </div>

                            <span class="detail-table__wave">〜</span>

                            <div class="detail-table__time-box-wrap">
                                <span class="detail-table__text">
                                    {{ $request->requested_clock_out ? \Carbon\Carbon::parse($request->requested_clock_out)->format('H:i') : '' }}
                                </span>
                            </div>
                        </div>
                    </td>
                </tr>

                @foreach ($requestBreaks as $index => $break)
                <tr class="detail-table__time-row">
                    <th>休憩{{ $index + 1 }}</th>
                    <td class="detail-table__value" colspan="3">
                        <div class="detail-table__time-row-inner">
                            <div class="detail-table__time-box-wrap">
                                <span class="detail-table__text">
                                    {{ $break->break_start ? \Carbon\Carbon::parse($break->break_start)->format('H:i') : '' }}
                                </span>
                            </div>

                            <span class="detail-table__wave">〜</span>

                            <div class="detail-table__time-box-wrap">
                                <span class="detail-table__text">
                                    {{ $break->break_end ? \Carbon\Carbon::parse($break->break_end)->format('H:i') : '' }}
                                </span>
                            </div>
                        </div>
                    </td>
                </tr>
                @endforeach

                <tr>
                    <th>備考</th>
                    <td colspan="3" class="detail-table__remark">
                        <span class="detail-table__text">
                            {{ $request->requested_note ?? '' }}
                        </span>
                    </td>
                </tr>
            </table>
        </div>

        <div class="attendance-detail__actions">
            @if (!$isApproved)
            <form method="POST" action="{{ route('admin.stamp_correction_request.approve', $request->id) }}">
                @csrf
                <button type="submit" class="edit-btn">承認</button>
            </form>
            @else
            <span class="edit-btn edit-btn--status">承認済み</span>
            @endif
        </div>

    </div>
</div>
@endsection
