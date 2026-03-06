<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
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

        $day = Carbon::parse($date)->locale('ja');

        return view('attendance.detail', compact('attendance', 'day'));
    }

    public function apply(Request $request, $date)
    {
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            abort(404);
        }

        $attendance = Attendance::with('breaks')
            ->where('user_id', Auth::id())
            ->where('date', $date)
            ->firstOrFail();

        if ($attendance->requested_at) {
            return redirect()->back()->withErrors(['※承認待ちのため修正はできません※']);
        }

        $request->validate([
            'clock_in'      => ['required'],
            'clock_out'     => ['required'],
            'break1_start'  => ['nullable'],
            'break1_end'    => ['nullable'],
            'break2_start'  => ['nullable'],
            'break2_end'    => ['nullable'],
            'remark'        => ['required', 'string', 'max:255'],
        ], [
            'clock_in.required'  => '出勤時間もしくは退勤時間が不適切な値です',
            'clock_out.required' => '出勤時間もしくは退勤時間が不適切な値です',
            'remark.required'    => '備考を記入してください',
        ]);

        if (strtotime($request->clock_in) >= strtotime($request->clock_out)) {
            return redirect()->back()
                ->withErrors(['出勤時間もしくは退勤時間が不適切な値です'])
                ->withInput();
        }

        $pairs = [
            ['s' => $request->break1_start, 'e' => $request->break1_end],
            ['s' => $request->break2_start, 'e' => $request->break2_end],
        ];

        foreach ($pairs as $p) {
            if (($p['s'] && !$p['e']) || (!$p['s'] && $p['e'])) {
                return redirect()->back()->withErrors(['休憩時間が不適切な値です'])->withInput();
            }
            if ($p['s'] && $p['e'] && strtotime($p['s']) >= strtotime($p['e'])) {
                return redirect()->back()->withErrors(['休憩時間が不適切な値です'])->withInput();
            }
        }

        $workStart = strtotime($request->clock_in);
        $workEnd   = strtotime($request->clock_out);

        foreach ($pairs as $p) {
            if (!$p['s'] && !$p['e']) {
                continue;
            }

            $bs = strtotime($p['s']);
            $be = strtotime($p['e']);

            if ($bs < $workStart || $be > $workEnd) {
                return redirect()->back()
                    ->withErrors(['休憩時間が勤務時間外です'])
                    ->withInput();
            }
        }

        $attendance->update([
            'clock_in'     => Carbon::parse($date . ' ' . $request->clock_in),
            'clock_out'    => Carbon::parse($date . ' ' . $request->clock_out),
            'remark'       => $request->remark,
            'requested_at' => now(),
        ]);

        $breaks = $attendance->breaks()->orderBy('break_start')->get();

        $breakInputs = [
            ['start' => $request->break1_start, 'end' => $request->break1_end],
            ['start' => $request->break2_start, 'end' => $request->break2_end],
        ];

        foreach ($breakInputs as $i => $bi) {
            if (!$bi['start'] && !$bi['end']) {
                continue;
            }

            $data = [
                'break_start' => Carbon::parse($date . ' ' . $bi['start']),
                'break_end'   => Carbon::parse($date . ' ' . $bi['end']),
            ];

            if (isset($breaks[$i])) {
                $breaks[$i]->update($data);
            } else {
                $attendance->breaks()->create($data);
            }
        }

        return redirect()->route('attendance.list');
    }
}
