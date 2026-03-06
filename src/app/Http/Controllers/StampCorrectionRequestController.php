<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use Illuminate\Support\Facades\Auth;

class StampCorrectionRequestController extends Controller
{
    public function index()
    {
        $pendingRequests = Attendance::where('user_id', Auth::id())
            ->whereNotNull('requested_at')
            ->orderByDesc('requested_at')
            ->get();

        $approvedRequests = collect();

        return view('stamp_correction_request.list', compact('pendingRequests', 'approvedRequests'));
    }
}
