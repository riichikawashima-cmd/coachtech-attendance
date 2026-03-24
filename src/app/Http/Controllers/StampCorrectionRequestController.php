<?php

namespace App\Http\Controllers;

use App\Models\CorrectionRequest;
use Illuminate\Support\Facades\Auth;

class StampCorrectionRequestController extends Controller
{
    public function index()
    {
        $pendingRequests = CorrectionRequest::with('attendance')
            ->where('user_id', Auth::id())
            ->where('status', 'pending')
            ->orderByDesc('created_at')
            ->paginate(10);

        $approvedRequests = CorrectionRequest::with('attendance')
            ->where('user_id', Auth::id())
            ->where('status', 'approved')
            ->orderByDesc('created_at')
            ->paginate(10);

        return view('stamp_correction_request.list', compact('pendingRequests', 'approvedRequests'));
    }
}
