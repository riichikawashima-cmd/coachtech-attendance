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
            ->get();

        $approvedRequests = CorrectionRequest::with(['user', 'attendance'])
            ->where('status', 'approved')
            ->latest()
            ->get();

        return view('admin.stamp_correction_requests.list', compact(
            'pendingRequests',
            'approvedRequests'
        ));
    }

    public function show($id)
    {
        $request = CorrectionRequest::with(['user', 'attendance.breaks'])
            ->findOrFail($id);

        return view('admin.stamp_correction_requests.approve', compact('request'));
    }

    public function approve($id)
    {
        $request = CorrectionRequest::with('attendance')
            ->findOrFail($id);

        $request->attendance->update([
            'clock_in' => $request->requested_clock_in,
            'clock_out' => $request->requested_clock_out,
            'remark' => $request->requested_note,
        ]);

        $request->update([
            'status' => 'approved',
        ]);

        return redirect('/admin/stamp_correction_request/list?tab=approved');
    }
}
