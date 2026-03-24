<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\CorrectionRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class AttendanceDetailController extends Controller
{
    public function index($date)
    {
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            abort(404);
        }

        $attendance = Attendance::with('breaks')
            ->where('user_id', Auth::id())
            ->where('date', $date)
            ->first();

        $pendingCorrectionRequest = null;
        $isLocked = false;

        if ($attendance) {
            $pendingCorrectionRequest = CorrectionRequest::with('breaks')
                ->where('attendance_id', $attendance->id)
                ->where('status', 'pending')
                ->latest()
                ->first();

            $isLocked = (bool) $pendingCorrectionRequest;
        }

        $day = Carbon::parse($date)->locale('ja');

        return view('attendance.detail', compact(
            'attendance',
            'day',
            'isLocked',
            'pendingCorrectionRequest'
        ));
    }

    public function apply(Request $request, $date)
    {
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            abort(404);
        }

        $attendance = Attendance::firstOrCreate(
            [
                'user_id' => Auth::id(),
                'date' => $date,
            ]
        );

        $attendance->load('breaks');

        if (CorrectionRequest::where('attendance_id', $attendance->id)->where('status', 'pending')->exists()) {
            return redirect()->back()->withErrors(['※承認待ちのため修正はできません※']);
        }

        $errors = [];

        if (!$request->clock_in || !$request->clock_out) {
            $errors['clock_in'] = '出勤時間もしくは退勤時間が不適切な値です';
        }

        if ($request->clock_in && $request->clock_out && strtotime($request->clock_in) >= strtotime($request->clock_out)) {
            $errors['clock_in'] = '出勤時間もしくは退勤時間が不適切な値です';
        }

        if (!$request->remark) {
            $errors['remark'] = '備考を記入してください';
        }

        $pairs = [];
        $index = 1;

        while ($request->has("break{$index}_start") || $request->has("break{$index}_end")) {
            $pairs[] = [
                'start_key' => "break{$index}_start",
                'end_key'   => "break{$index}_end",
            ];
            $index++;
        }

        foreach ($pairs as $pair) {
            $start = $request->input($pair['start_key']);
            $end   = $request->input($pair['end_key']);

            if (($start && !$end) || (!$start && $end)) {
                $errors[$pair['start_key']] = '休憩時間が不適切な値です';
                continue;
            }

            if ($start && $end && strtotime($start) >= strtotime($end)) {
                $errors[$pair['start_key']] = '休憩時間が不適切な値です';
                continue;
            }

            if ($request->clock_in && $request->clock_out && $start && $end) {
                $workStart = strtotime($request->clock_in);
                $workEnd = strtotime($request->clock_out);
                $breakStart = strtotime($start);
                $breakEnd = strtotime($end);

                if ($breakStart < $workStart || $breakEnd > $workEnd) {
                    $errors[$pair['start_key']] = '休憩時間が勤務時間外です';
                }
            }
        }

        if (!empty($errors)) {
            return redirect()->back()
                ->withErrors($errors)
                ->withInput();
        }

        $correctionRequest = CorrectionRequest::create([
            'attendance_id'       => $attendance->id,
            'user_id'             => Auth::id(),
            'requested_clock_in'  => Carbon::parse($date . ' ' . $request->clock_in),
            'requested_clock_out' => Carbon::parse($date . ' ' . $request->clock_out),
            'requested_note'      => $request->remark,
            'status'              => 'pending',
        ]);

        $breaks = [];
        $index = 1;

        while ($request->has("break{$index}_start") || $request->has("break{$index}_end")) {
            $start = $request->input("break{$index}_start");
            $end   = $request->input("break{$index}_end");

            if ($start && $end) {
                $breaks[] = [
                    'start' => $start,
                    'end'   => $end,
                ];
            }

            $index++;
        }

        foreach ($breaks as $b) {
            $correctionRequest->breaks()->create([
                'break_start' => Carbon::parse($date . ' ' . $b['start']),
                'break_end'   => Carbon::parse($date . ' ' . $b['end']),
            ]);
        }

        return redirect()->route('attendance.list');
    }
}
