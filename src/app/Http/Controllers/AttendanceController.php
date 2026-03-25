<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use Illuminate\Support\Facades\Auth;

class AttendanceController extends Controller
{
    public function index()
    {
        $now = now()->locale('ja');
        $today = $now->toDateString();

        $attendance = Attendance::where('user_id', Auth::id())
            ->where('date', $today)
            ->first();

        if (!$attendance) {
            $status = '勤務外';
        } elseif ($attendance->clock_out) {
            $status = '退勤済';
        } elseif ($attendance->breaks()->whereNull('break_end')->exists()) {
            $status = '休憩中';
        } else {
            $status = '出勤中';
        }

        return view('attendance.index', compact('now', 'status'));
    }

    public function clockIn()
    {
        $today = now()->toDateString();

        Attendance::firstOrCreate(
            [
                'user_id' => Auth::id(),
                'date' => $today,
            ],
            [
                'clock_in' => now(),
            ]
        );

        return redirect('/attendance');
    }

    public function breakStart()
    {
        $now = now();
        $today = $now->toDateString();

        $attendance = Attendance::where('user_id', Auth::id())
            ->where('date', $today)
            ->firstOrFail();

        $attendance->breaks()->create([
            'break_start' => $now,
        ]);

        return redirect('/attendance');
    }

    public function breakEnd()
    {
        $now = now();
        $today = $now->toDateString();

        $attendance = Attendance::where('user_id', Auth::id())
            ->where('date', $today)
            ->firstOrFail();

        $break = $attendance->breaks()
            ->whereNull('break_end')
            ->latest('break_start')
            ->firstOrFail();

        $break->update([
            'break_end' => $now,
        ]);

        return redirect('/attendance');
    }

    public function clockOut()
    {
        $now = now();
        $today = $now->toDateString();

        $attendance = Attendance::where('user_id', Auth::id())
            ->where('date', $today)
            ->firstOrFail();

        $attendance->update([
            'clock_out' => $now,
        ]);

        return redirect('/attendance');
    }
}
