@extends('layouts.app')

<link rel="stylesheet" href="{{ asset('css/attendance.css') }}">

@section('content')
<div class="attendance">
    <div>

        <div class="attendance__status">
            {{ $status }}
        </div>

        <div class="attendance__date">
            {{ $now->isoFormat('Y年M月D日(ddd)') }}
        </div>

        <div class="attendance__time" id="clock">{{ $now->format('H:i') }}</div>

        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const el = document.getElementById('clock');
                const pad = (n) => String(n).padStart(2, '0');

                function updateClock() {
                    const d = new Date();
                    el.textContent = `${pad(d.getHours())}:${pad(d.getMinutes())}`;
                }
                updateClock();
                setInterval(updateClock, 60000);
            });
        </script>

        <div class="attendance__actions">

            @if ($status === '勤務外')
            <form method="POST" action="/attendance/clock-in">
                @csrf
                <button type="submit">出勤</button>
            </form>
            @endif

            @if ($status === '勤務中')
            <div class="attendance__actions-row">
                <form method="POST" action="/attendance/clock-out">
                    @csrf
                    <button type="submit" class="attendance__button attendance__button--primary">
                        退勤
                    </button>
                </form>

                <form method="POST" action="/attendance/break-start">
                    @csrf
                    <button type="submit" class="attendance__button attendance__button--secondary">
                        休憩入
                    </button>
                </form>
            </div>
            @endif

            @if ($status === '休憩中')
            <form method="POST" action="/attendance/break-end">
                @csrf
                <button type="submit" class="attendance__button attendance__button--secondary">
                    休憩戻
                </button>
            </form>
            @endif

            @if ($status === '退勤済')
            <p class="attendance__thanks">お疲れ様でした。</p>
            @endif

        </div>

    </div>
</div>
@endsection