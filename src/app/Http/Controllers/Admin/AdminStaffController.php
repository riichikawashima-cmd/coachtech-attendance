<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Attendance;

class AdminStaffController extends Controller
{
    public function index()
    {
        $users = User::select('id', 'name', 'email')->get();

        return view('admin.staff.list', compact('users'));
    }

    public function show(Request $request, $id)
    {
        $base = now();

        if ($request->filled('month')) {
            $base = \Carbon\Carbon::createFromFormat('Y-m', $request->month);
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

        $attendances = Attendance::with('breaks')
            ->where('user_id', $id)
            ->where('date', 'like', $month . '%')
            ->orderBy('date', 'asc')
            ->get();

        $filename = 'attendance_' . $id . '_' . $month . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename={$filename}",
        ];

        $callback = function () use ($attendances) {
            $file = fopen('php://output', 'w');

            fputcsv($file, ['日付', '出勤', '退勤']);

            foreach ($attendances as $attendance) {
                fputcsv($file, [
                    $attendance->date,
                    $attendance->clock_in,
                    $attendance->clock_out,
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
