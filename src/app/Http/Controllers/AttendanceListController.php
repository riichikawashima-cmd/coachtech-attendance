<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class AttendanceListController extends Controller
{
    public function index(Request $request)
    {
        $base = now()->locale('ja');

        if ($request->filled('month')) {
            $base = Carbon::createFromFormat('Y-m', $request->month)->locale('ja');
        }

        $start = $base->copy()->startOfMonth();
        $end   = $base->copy()->endOfMonth();

        $attendancesByDate = Attendance::with('breaks')
            ->where('user_id', Auth::id())
            ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->get()
            ->keyBy('date');

        $dates = [];
        for ($d = $start->copy(); $d->lte($end); $d->addDay()) {
            $dates[] = $d->copy();
        }

        $monthLabel = $base->format('Y/m');
        $prevMonth  = $base->copy()->subMonth()->format('Y-m');
        $nextMonth  = $base->copy()->addMonth()->format('Y-m');

        return view('attendance.list', compact(
            'dates',
            'attendancesByDate',
            'monthLabel',
            'prevMonth',
            'nextMonth'
        ));
    }
}
