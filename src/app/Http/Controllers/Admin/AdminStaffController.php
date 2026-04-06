<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;

class AdminStaffController extends Controller
{
    public function index()
    {
        $users = User::select('id', 'name', 'email')
            ->paginate(10);

        return view('admin.staff.list', compact('users'));
    }

    public function show(Request $request, $id)
    {
        $base = now();

        if ($request->filled('month')) {
            $base = Carbon::createFromFormat('Y-m', $request->month);
        }

        $start = $base->copy()->startOfMonth();
        $end = $base->copy()->endOfMonth();

        $attendancesByDate = Attendance::with('breaks')
            ->where('user_id', $id)
            ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->get()
            ->keyBy('date');

        $dates = [];
        for ($d = $start->copy(); $d->lte($end); $d->addDay()) {
            $dates[] = $d->copy();
        }

        return view('admin.staff.staff_list', compact(
            'dates',
            'attendancesByDate',
            'id'
        ));
    }

    public function csv(Request $request, $id)
    {
        $month = $request->input('month', now()->format('Y-m'));
        $user = User::findOrFail($id);

        $attendances = Attendance::with('breaks')
            ->where('user_id', $id)
            ->where('date', 'like', $month . '%')
            ->orderBy('date', 'asc')
            ->get();

        $filename = 'attendance_' . $id . '_' . $month . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename={$filename}",
        ];

        $callback = function () use ($attendances, $user) {
            $file = fopen('php://output', 'w');

            fputcsv($file, ['スタッフ名：' . $user->name]);
            fputcsv($file, []);
            fputcsv($file, ['日付', '出勤', '退勤', '休憩時間', '合計', '備考']);

            $weekDays = ['日', '月', '火', '水', '木', '金', '土'];

            foreach ($attendances as $attendance) {
                $date = Carbon::parse($attendance->date);
                $formattedDate = $date->format('Y/m/d') . '（' . $weekDays[$date->dayOfWeek] . '）';

                $clockIn = $attendance->clock_in
                    ? Carbon::parse($attendance->clock_in)->format('H:i')
                    : '';

                $clockOut = $attendance->clock_out
                    ? Carbon::parse($attendance->clock_out)->format('H:i')
                    : '';

                $breakMinutes = 0;
                foreach ($attendance->breaks as $break) {
                    if ($break->break_start && $break->break_end) {
                        $breakMinutes += Carbon::parse($break->break_start)
                            ->diffInMinutes(Carbon::parse($break->break_end));
                    }
                }

                $breakTime = sprintf('%02d:%02d', floor($breakMinutes / 60), $breakMinutes % 60);

                $workMinutes = 0;
                if ($attendance->clock_in && $attendance->clock_out) {
                    $totalMinutes = Carbon::parse($attendance->clock_in)
                        ->diffInMinutes(Carbon::parse($attendance->clock_out));

                    $workMinutes = max($totalMinutes - $breakMinutes, 0);
                }

                $workTime = sprintf('%02d:%02d', floor($workMinutes / 60), $workMinutes % 60);

                fputcsv($file, [
                    $formattedDate,
                    $clockIn,
                    $clockOut,
                    $breakTime,
                    $workTime,
                    $attendance->note ?? '',
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
