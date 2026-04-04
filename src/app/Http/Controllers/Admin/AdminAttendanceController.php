<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\User;
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

        $attendance = Attendance::with(['user', 'breaks'])
            ->where('user_id', $id)
            ->where('date', $date)
            ->first();

        if (!$attendance) {
            $attendance = new Attendance([
                'user_id' => $id,
                'date' => $date,
            ]);
            $attendance->setRelation('user', User::findOrFail($id));
            $attendance->setRelation('breaks', collect());
        }

        return view('admin.attendance.detail', compact('attendance'));
    }

    public function storeByDate(Request $request)
    {
        $errors = [];

        if (!$request->user_id || !User::where('id', $request->user_id)->exists()) {
            $errors['user_id'] = 'ユーザーが不正です';
        }

        if (!$request->date || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $request->date)) {
            $errors['date'] = '日付の形式が不適切です';
        }

        if ($request->clock_in && !preg_match('/^\d{2}:\d{2}$/', $request->clock_in)) {
            $errors['clock_in'] = '出勤時間の形式が不適切です';
        }

        if ($request->clock_out && !preg_match('/^\d{2}:\d{2}$/', $request->clock_out)) {
            $errors['clock_out'] = '退勤時間の形式が不適切です';
        }

        if (!$request->clock_in || !$request->clock_out) {
            $errors['clock_in'] = '出勤時間もしくは退勤時間が不適切な値です';
        }

        if (
            $request->clock_in &&
            $request->clock_out &&
            preg_match('/^\d{2}:\d{2}$/', $request->clock_in) &&
            preg_match('/^\d{2}:\d{2}$/', $request->clock_out) &&
            strtotime($request->clock_in) >= strtotime($request->clock_out)
        ) {
            $errors['clock_in'] = '出勤時間もしくは退勤時間が不適切な値です';
        }

        if (!$request->note) {
            $errors['note'] = '備考を記入してください';
        }

        $breaks = $request->input('breaks', []);

        foreach ($breaks as $index => $break) {
            $breakStart = $break['break_start'] ?? null;
            $breakEnd = $break['break_end'] ?? null;

            if ($breakStart && !preg_match('/^\d{2}:\d{2}$/', $breakStart)) {
                $errors["breaks.$index.break_start"] = '休憩開始時間の形式が不適切です';
                continue;
            }

            if ($breakEnd && !preg_match('/^\d{2}:\d{2}$/', $breakEnd)) {
                $errors["breaks.$index.break_end"] = '休憩終了時間の形式が不適切です';
                continue;
            }

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
                preg_match('/^\d{2}:\d{2}$/', $request->clock_in) &&
                preg_match('/^\d{2}:\d{2}$/', $request->clock_out) &&
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

        $attendance = Attendance::firstOrCreate([
            'user_id' => $request->user_id,
            'date' => $request->date,
        ]);

        return $this->update($request, $attendance->id);
    }

    public function update(Request $request, $id)
    {
        $attendance = Attendance::with('breaks')->findOrFail($id);

        $errors = [];

        if (!$request->date || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $request->date)) {
            $errors['date'] = '日付の形式が不適切です';
        }

        if ($request->clock_in && !preg_match('/^\d{2}:\d{2}$/', $request->clock_in)) {
            $errors['clock_in'] = '出勤時間の形式が不適切です';
        }

        if ($request->clock_out && !preg_match('/^\d{2}:\d{2}$/', $request->clock_out)) {
            $errors['clock_out'] = '退勤時間の形式が不適切です';
        }

        if (!$request->clock_in || !$request->clock_out) {
            $errors['clock_in'] = '出勤時間もしくは退勤時間が不適切な値です';
        }

        if (
            $request->clock_in &&
            $request->clock_out &&
            preg_match('/^\d{2}:\d{2}$/', $request->clock_in) &&
            preg_match('/^\d{2}:\d{2}$/', $request->clock_out) &&
            strtotime($request->clock_in) >= strtotime($request->clock_out)
        ) {
            $errors['clock_in'] = '出勤時間もしくは退勤時間が不適切な値です';
        }

        if (!$request->note) {
            $errors['note'] = '備考を記入してください';
        }

        $breaks = $request->input('breaks', []);

        foreach ($breaks as $index => $break) {
            $breakStart = $break['break_start'] ?? null;
            $breakEnd = $break['break_end'] ?? null;

            if ($breakStart && !preg_match('/^\d{2}:\d{2}$/', $breakStart)) {
                $errors["breaks.$index.break_start"] = '休憩開始時間の形式が不適切です';
                continue;
            }

            if ($breakEnd && !preg_match('/^\d{2}:\d{2}$/', $breakEnd)) {
                $errors["breaks.$index.break_end"] = '休憩終了時間の形式が不適切です';
                continue;
            }

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
                preg_match('/^\d{2}:\d{2}$/', $request->clock_in) &&
                preg_match('/^\d{2}:\d{2}$/', $request->clock_out) &&
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

            $existingBreaks = $attendance->breaks->values();

            foreach ($breaks as $index => $break) {
                $breakStart = $break['break_start'] ?? null;
                $breakEnd = $break['break_end'] ?? null;

                $existingBreak = $existingBreaks->get($index);

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

            if ($existingBreaks->count() > count($breaks)) {
                for ($i = count($breaks); $i < $existingBreaks->count(); $i++) {
                    $existingBreak = $existingBreaks->get($i);

                    if ($existingBreak) {
                        $existingBreak->delete();
                    }
                }
            }
        });

        return redirect('/admin/attendance/' . $attendance->id);
    }
}
