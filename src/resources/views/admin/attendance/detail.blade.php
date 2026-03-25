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

        @php
        $day = \Carbon\Carbon::parse($attendance->date)->locale('ja');
        $breaks = $attendance->breaks;
        $breakCount = $breaks->count() + 1;
        @endphp

        <form method="POST" action="/admin/attendance/{{ $attendance->id }}">
            @csrf
            <input type="hidden" name="date" value="{{ $attendance->date }}">

            <div class="attendance-detail__card">
                <table class="detail-table">
                    <tr>
                        <th>名前</th>
                        <td class="detail-table__value" colspan="3">
                            <div class="detail-table__name">
                                {{ $attendance->user->name }}
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
                                    <div class="time-field">
                                        <input
                                            type="text"
                                            class="time-box time-input"
                                            name="clock_in"
                                            value="{{ old('clock_in', $attendance->clock_in ? \Carbon\Carbon::parse($attendance->clock_in)->format('H:i') : '') }}">
                                        @error('clock_in')
                                        <div class="input-error">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <span class="detail-table__wave">〜</span>

                                <div class="detail-table__time-box-wrap">
                                    <div class="time-field">
                                        <input
                                            type="text"
                                            class="time-box time-input"
                                            name="clock_out"
                                            value="{{ old('clock_out', $attendance->clock_out ? \Carbon\Carbon::parse($attendance->clock_out)->format('H:i') : '') }}">
                                        @error('clock_out')
                                        <div class="input-error">{{ $message }}</div>
                                        @enderror
                                    </div>
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
                                    <div class="time-field">
                                        <input
                                            type="text"
                                            class="time-box time-input"
                                            name="breaks[{{ $i }}][break_start]"
                                            value="{{ old('breaks.' . $i . '.break_start', isset($breaks[$i]) && $breaks[$i]->break_start ? \Carbon\Carbon::parse($breaks[$i]->break_start)->format('H:i') : '') }}">
                                        @error('breaks.' . $i . '.break_start')
                                        <div class="input-error">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <span class="detail-table__wave">〜</span>

                                <div class="detail-table__time-box-wrap">
                                    <div class="time-field">
                                        <input
                                            type="text"
                                            class="time-box time-input"
                                            name="breaks[{{ $i }}][break_end]"
                                            value="{{ old('breaks.' . $i . '.break_end', isset($breaks[$i]) && $breaks[$i]->break_end ? \Carbon\Carbon::parse($breaks[$i]->break_end)->format('H:i') : '') }}">
                                        @error('breaks.' . $i . '.break_end')
                                        <div class="input-error">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </td>
                        </tr>
                        @endfor

                        <tr>
                            <th>備考</th>
                            <td colspan="3" class="detail-table__remark">
                                <textarea class="remark-box" name="note">{{ old('note', $attendance->note ?? '') }}</textarea>
                                @error('note')
                                <div class="input-error">{{ $message }}</div>
                                @enderror
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