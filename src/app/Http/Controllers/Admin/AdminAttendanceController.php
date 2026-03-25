<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AdminAttendanceController extends Controller
{
    public function index(Request $request)
    {
        $date = $request->input('date', Carbon::today()->toDateString());

        $attendances = Attendance::with(['user', 'breaks'])
            ->whereDate('date', $date)
            ->paginate(10);

        return view('admin.attendance.list', compact('attendances', 'date'));
    }

    public function show($id)
    {
        $attendance = Attendance::with(['user', 'breaks'])->findOrFail($id);

        return view('admin.attendance.detail', compact('attendance'));
    }

    public function showByDate($id, $date)
    {
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            abort(404);
        }

        $attendance = Attendance::with(['user', 'breaks'])->firstOrCreate(
            [
                'user_id' => $id,
                'date' => $date,
            ]
        );

        return view('admin.attendance.detail', compact('attendance'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'date' => ['required', 'date'],
            'clock_in' => ['nullable', 'date_format:H:i'],
            'clock_out' => ['nullable', 'date_format:H:i'],
            'note' => ['nullable', 'string'],
            'breaks.*.break_start' => ['nullable', 'date_format:H:i'],
            'breaks.*.break_end' => ['nullable', 'date_format:H:i'],
        ]);

        $attendance = Attendance::with('breaks')->findOrFail($id);

        $errors = [];

        if (!$request->clock_in || !$request->clock_out) {
            $errors['clock_in'] = '出勤時間もしくは退勤時間が不適切な値です';
        }

        if (
            $request->clock_in &&
            $request->clock_out &&
            strtotime($request->clock_in) >= strtotime($request->clock_out)
        ) {
            $errors['clock_in'] = '出勤時間もしくは退勤時間が不適切な値です';
        }

        if (!$request->note) {
            $errors['note'] = '備考を記入してください';
        }

        $breaks = $request->input('breaks', []);

        foreach ([0, 1] as $index) {
            $breakStart = $breaks[$index]['break_start'] ?? null;
            $breakEnd = $breaks[$index]['break_end'] ?? null;

            if (($breakStart && !$breakEnd) || (!$breakStart && $breakEnd)) {
                $errors["breaks.$index.break_start"] = '休憩時間が不適切な値です';
                continue;
            }

            if (
                $breakStart &&
                $breakEnd &&
                strtotime($breakStart) >= strtotime($breakEnd)
            ) {
                $errors["breaks.$index.break_start"] = '休憩時間が不適切な値です';
                continue;
            }

            if (
                $request->clock_in &&
                $request->clock_out &&
                $breakStart &&
                $breakEnd
            ) {
                $workStart = strtotime($request->clock_in);
                $workEnd = strtotime($request->clock_out);
                $breakStartTime = strtotime($breakStart);
                $breakEndTime = strtotime($breakEnd);

                if ($breakStartTime < $workStart || $breakEndTime > $workEnd) {
                    $errors["breaks.$index.break_start"] = '休憩時間が勤務時間外です';
                }
            }
        }

        if (!empty($errors)) {
            return redirect()->back()
                ->withErrors($errors)
                ->withInput();
        }

        DB::transaction(function () use ($request, $attendance, $breaks) {
            $attendance->update([
                'date' => $request->date,
                'clock_in' => Carbon::parse($request->date . ' ' . $request->clock_in),
                'clock_out' => Carbon::parse($request->date . ' ' . $request->clock_out),
                'note' => $request->note,
            ]);

            foreach ([0, 1] as $index) {
                $breakStart = $breaks[$index]['break_start'] ?? null;
                $breakEnd = $breaks[$index]['break_end'] ?? null;

                $existingBreak = $attendance->breaks->get($index);

                if ($breakStart && $breakEnd) {
                    if ($existingBreak) {
                        $existingBreak->update([
                            'break_start' => Carbon::parse($request->date . ' ' . $breakStart),
                            'break_end' => Carbon::parse($request->date . ' ' . $breakEnd),
                        ]);
                    } else {
                        $attendance->breaks()->create([
                            'break_start' => Carbon::parse($request->date . ' ' . $breakStart),
                            'break_end' => Carbon::parse($request->date . ' ' . $breakEnd),
                        ]);
                    }
                } elseif ($existingBreak) {
                    $existingBreak->delete();
                }
            }
        });

        return redirect('/admin/attendance/' . $id);
    }
}
