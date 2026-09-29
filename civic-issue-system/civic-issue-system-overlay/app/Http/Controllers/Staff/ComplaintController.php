<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Complaint;
use App\Notifications\ComplaintStatusChanged;
use Illuminate\Http\Request;

class ComplaintController extends Controller
{
    public function index(Request $request)
    {
        $complaints = $request->user()->assignedComplaints()
            ->with('user', 'department')
            ->when($request->status, fn ($q, $s) => $q->where('status', $s))
            ->orderByRaw("FIELD(priority, 'high', 'medium', 'low')")
            ->latest()->paginate(15)->withQueryString();

        return view('staff.complaints.index', compact('complaints'));
    }

    public function show(Request $request, Complaint $complaint)
    {
        abort_unless($complaint->assigned_to === $request->user()->id, 403);

        $complaint->load('user', 'department', 'updates.user');

        return view('staff.complaints.show', compact('complaint'));
    }

    public function updateStatus(Request $request, Complaint $complaint)
    {
        abort_unless($complaint->assigned_to === $request->user()->id, 403);

        $data = $request->validate([
            'status' => 'required|in:in_progress,resolved,rejected',
            'remarks' => 'required|string|max:1000',
        ]);

        $complaint->update([
            'status' => $data['status'],
            'resolved_at' => $data['status'] === 'resolved' ? now() : null,
        ]);

        $complaint->updates()->create([
            'user_id' => $request->user()->id,
            'status' => $data['status'],
            'remarks' => $data['remarks'],
        ]);

        $complaint->user->notify(new ComplaintStatusChanged(
            $complaint, "Your complaint is now {$complaint->status_label}: {$data['remarks']}"
        ));

        return back()->with('success', 'Status updated and citizen notified.');
    }
}
