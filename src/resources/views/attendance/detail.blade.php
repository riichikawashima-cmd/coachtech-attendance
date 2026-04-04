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

        <form method="POST" action="{{ route('attendance.apply', ['date' => $day->toDateString()]) }}">
            @csrf

            <div class="attendance-detail__card">

                @php
                $breaks = $attendance ? $attendance->breaks : collect();
                $pendingBreaks = $pendingCorrectionRequest ? $pendingCorrectionRequest->breaks : collect();
                $breakCount = $isLocked ? max($pendingBreaks->count(), 1) : ($breaks->count() + 1);
                @endphp

                <table class="detail-table">
                    <tr>
                        <th>名前</th>
                        <td class="detail-table__value" colspan="3">
                            <div class="detail-table__name">
                                {{ Auth::user()->name }}
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
                                    @if($isLocked)
                                    <span class="detail-table__text">
                                        {{ $pendingCorrectionRequest && $pendingCorrectionRequest->requested_clock_in ? \Carbon\Carbon::parse($pendingCorrectionRequest->requested_clock_in)->format('H:i') : '' }}
                                    </span>
                                    @else
                                    <div class="time-field">
                                        <input
                                            type="text"
                                            class="time-box time-input"
                                            name="clock_in"
                                            value="{{ old('clock_in', $attendance && $attendance->clock_in ? \Carbon\Carbon::parse($attendance->clock_in)->format('H:i') : '') }}">
                                        @error('clock_in')
                                        <div class="input-error">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    @endif
                                </div>

                                <span class="detail-table__wave">〜</span>

                                <div class="detail-table__time-box-wrap">
                                    @if($isLocked)
                                    <span class="detail-table__text">
                                        {{ $pendingCorrectionRequest && $pendingCorrectionRequest->requested_clock_out ? \Carbon\Carbon::parse($pendingCorrectionRequest->requested_clock_out)->format('H:i') : '' }}
                                    </span>
                                    @else
                                    <div class="time-field">
                                        <input
                                            type="text"
                                            class="time-box time-input"
                                            name="clock_out"
                                            value="{{ old('clock_out', $attendance && $attendance->clock_out ? \Carbon\Carbon::parse($attendance->clock_out)->format('H:i') : '') }}">
                                        @error('clock_out')
                                        <div class="input-error">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </td>
                    </tr>

                    @for ($i = 0; $i < $breakCount; $i++)
                        <tr class="detail-table__time-row">
                        <th>休憩{{ $i + 1 }}</th>
                        <td class="detail-table__value" colspan="3">
                            <div class="detail-table__time-row-inner">
                                <div class="detail-table__time-box-wrap">
                                    @if($isLocked)
                                    <span class="detail-table__text">
                                        {{ isset($pendingBreaks[$i]) && $pendingBreaks[$i]->break_start ? \Carbon\Carbon::parse($pendingBreaks[$i]->break_start)->format('H:i') : '' }}
                                    </span>
                                    @else
                                    <div class="time-field">
                                        <input
                                            type="text"
                                            class="time-box time-input"
                                            name="break{{ $i + 1 }}_start"
                                            value="{{ old('break' . ($i + 1) . '_start', isset($breaks[$i]) && $breaks[$i]->break_start ? \Carbon\Carbon::parse($breaks[$i]->break_start)->format('H:i') : '') }}">
                                        @error('break' . ($i + 1) . '_start')
                                        <div class="input-error">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    @endif
                                </div>

                                <span class="detail-table__wave">〜</span>

                                <div class="detail-table__time-box-wrap">
                                    @if($isLocked)
                                    <span class="detail-table__text">
                                        {{ isset($pendingBreaks[$i]) && $pendingBreaks[$i]->break_end ? \Carbon\Carbon::parse($pendingBreaks[$i]->break_end)->format('H:i') : '' }}
                                    </span>
                                    @else
                                    <div class="time-field">
                                        <input
                                            type="text"
                                            class="time-box time-input"
                                            name="break{{ $i + 1 }}_end"
                                            value="{{ old('break' . ($i + 1) . '_end', isset($breaks[$i]) && $breaks[$i]->break_end ? \Carbon\Carbon::parse($breaks[$i]->break_end)->format('H:i') : '') }}">
                                        @error('break' . ($i + 1) . '_end')
                                        <div class="input-error">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </td>
                        </tr>
                        @endfor

                        <tr>
                            <th>備考</th>
                            <td colspan="3" class="detail-table__remark">
                                @if($isLocked)
                                <span class="detail-table__text">
                                    {{ $pendingCorrectionRequest->requested_note ?? '' }}
                                </span>
                                @else
                                <textarea class="remark-box" name="remark">{{ old('remark', $attendance->note ?? '') }}</textarea>
                                @error('remark')
                                <div class="input-error">{{ $message }}</div>
                                @enderror
                                @endif
                            </td>
                        </tr>
                </table>

            </div>

            <div class="attendance-detail__actions">
                @if (!$isLocked)
                <button type="submit" class="edit-btn">修正</button>
                @endif
            </div>
        </form>

        @if ($isLocked)
        <div class="approval-message">
            ※承認待ちのため修正はできません。
        </div>
        @endif

    </div>
</div>

<script>
    document.querySelectorAll('.time-input').forEach(input => {
        input.addEventListener('input', function() {
            let v = this.value.replace(/[^0-9]/g, '');

            if (v.length >= 3) {
                this.value = v.slice(0, 2) + ':' + v.slice(2, 4);
            } else {
                this.value = v;
            }
        });
    });
</script>

@endsection