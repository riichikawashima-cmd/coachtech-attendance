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
                $isLocked = $attendance && $attendance->requested_at;
                $breaks = $attendance ? $attendance->breaks : collect();
                @endphp

                <table class="detail-table">
                    <colgroup>
                        <col class="col-label">
                        <col class="col-left">
                        <col class="col-sep">
                        <col class="col-right">
                    </colgroup>

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
                            <input
                                type="text"
                                class="time-box time-input"
                                name="clock_in"
                                value="{{ old('clock_in', $attendance && $attendance->clock_in ? \Carbon\Carbon::parse($attendance->clock_in)->format('H:i') : '') }}"
                                @if($isLocked) disabled @endif>
                            @error('clock_in')
                            <div class="input-error">{{ $message }}</div>
                            @enderror
                        </td>
                        <td class="detail-table__sep detail-table__sep--mark"></td>
                        <td class="detail-table__time">
                            <input
                                type="text"
                                class="time-box time-input"
                                name="clock_out"
                                value="{{ old('clock_out', $attendance && $attendance->clock_out ? \Carbon\Carbon::parse($attendance->clock_out)->format('H:i') : '') }}"
                                @if($isLocked) disabled @endif>
                            @error('clock_out')
                            <div class="input-error">{{ $message }}</div>
                            @enderror
                        </td>
                    </tr>

                    @php
                    $breakCount = $breaks->count() + 1;
                    @endphp

                    @for ($i = 0; $i < $breakCount; $i++)
                        <tr>
                        <th>休憩{{ $i + 1 }}</th>
                        <td class="detail-table__time">
                            <input
                                type="text"
                                class="time-box time-input"
                                name="break{{ $i + 1 }}_start"
                                value="{{ old('break' . ($i + 1) . '_start', isset($breaks[$i]) && $breaks[$i]->break_start ? \Carbon\Carbon::parse($breaks[$i]->break_start)->format('H:i') : '') }}"
                                @if($isLocked) disabled @endif>
                            @error('break' . ($i + 1) . '_start')
                            <div class="input-error">{{ $message }}</div>
                            @enderror
                        </td>
                        <td class="detail-table__sep detail-table__sep--mark"></td>
                        <td class="detail-table__time">
                            <input
                                type="text"
                                class="time-box time-input"
                                name="break{{ $i + 1 }}_end"
                                value="{{ old('break' . ($i + 1) . '_end', isset($breaks[$i]) && $breaks[$i]->break_end ? \Carbon\Carbon::parse($breaks[$i]->break_end)->format('H:i') : '') }}"
                                @if($isLocked) disabled @endif>
                            @error('break' . ($i + 1) . '_end')
                            <div class="input-error">{{ $message }}</div>
                            @enderror
                        </td>
                        </tr>
                        @endfor

                        <tr>
                            <th>備考</th>
                            <td colspan="3" class="detail-table__remark">
                                <textarea class="remark-box" name="remark" @if($isLocked) disabled @endif>{{ old('remark', $attendance->remark ?? '') }}</textarea>
                                @error('remark')
                                <div class="input-error">{{ $message }}</div>
                                @enderror
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