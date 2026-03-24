<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CorrectionRequest;
use Illuminate\Support\Facades\Auth;

class AdminStampCorrectionRequestController extends Controller
{
    public function index()
    {
        $pendingRequests = CorrectionRequest::with(['user', 'attendance'])
            ->where('status', 'pending')
            ->latest()
            ->paginate(10, ['*'], 'pending_page');

        $approvedRequests = CorrectionRequest::with(['user', 'attendance'])
            ->where('status', 'approved')
            ->latest()
            ->paginate(10, ['*'], 'approved_page');

        return view('admin.stamp_correction_requests.list', compact(
            'pendingRequests',
            'approvedRequests'
        ));
    }

    public function show($id)
    {
        $request = CorrectionRequest::with(['user', 'attendance.breaks', 'breaks'])
            ->findOrFail($id);

        return view('admin.stamp_correction_requests.approve', compact('request'));
    }

    public function approve($id)
    {
        $request = CorrectionRequest::with(['attendance.breaks', 'breaks'])
            ->findOrFail($id);

        $attendance = $request->attendance;

        $attendance->update([
            'clock_in' => $request->requested_clock_in,
            'clock_out' => $request->requested_clock_out,
            'remark' => $request->requested_note,
        ]);

        $attendance->breaks()->delete();

        foreach ($request->breaks as $break) {
            $attendance->breaks()->create([
                'break_start' => $break->break_start,
                'break_end'   => $break->break_end,
            ]);
        }

        $request->update([
            'status' => 'approved',
        ]);

        return redirect('/admin/stamp_correction_request/list?tab=approved');
    }
}
