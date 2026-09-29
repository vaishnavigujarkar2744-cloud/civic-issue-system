<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Complaint;
use App\Notifications\ComplaintStatusChanged;
use App\Services\AnalyticsService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ComplaintApiController extends Controller
{
    /** Scopes results by role: citizen = own, staff = assigned, admin = all. */
    private function scoped(Request $request)
    {
        $user = $request->user();
        $query = Complaint::query();

        return match ($user->role) {
            'citizen' => $query->where('user_id', $user->id),
            'staff' => $query->where('assigned_to', $user->id),
            default => $query,
        };
    }

    private function authorizeView(Request $request, Complaint $complaint): void
    {
        $user = $request->user();

        abort_unless(
            $user->isAdmin()
            || ($user->isCitizen() && $complaint->user_id === $user->id)
            || ($user->isStaff() && $complaint->assigned_to === $user->id),
            403
        );
    }

    public function index(Request $request)
    {
        return $this->scoped($request)
            ->with('department:id,name', 'assignee:id,name')
            ->when($request->status, fn ($q, $s) => $q->where('status', $s))
            ->when($request->department_id, fn ($q, $d) => $q->where('department_id', $d))
            ->latest()->paginate($request->integer('per_page', 15));
    }

    public function show(Request $request, Complaint $complaint)
    {
        $this->authorizeView($request, $complaint);

        return $complaint->load('user:id,name', 'department:id,name', 'assignee:id,name', 'updates.user:id,name');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:150',
            'category' => ['required', Rule::in(Complaint::CATEGORIES)],
            'description' => 'required|string|max:2000',
            'address' => 'required|string|max:255',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:4096',
        ]);

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('complaints', 'public');
        }

        $complaint = $request->user()->complaints()->create($data + ['status' => 'pending']);
        $complaint->updates()->create([
            'user_id' => $request->user()->id, 'status' => 'pending', 'remarks' => 'Complaint submitted.',
        ]);

        return response()->json($complaint, 201);
    }

    public function updateStatus(Request $request, Complaint $complaint)
    {
        $user = $request->user();
        abort_unless($user->isAdmin() || $complaint->assigned_to === $user->id, 403);

        $data = $request->validate([
            'status' => ['required', Rule::in(array_keys(Complaint::STATUS_LABELS))],
            'remarks' => 'required|string|max:1000',
        ]);

        $complaint->update([
            'status' => $data['status'],
            'resolved_at' => $data['status'] === 'resolved' ? now() : null,
        ]);
        $complaint->updates()->create(['user_id' => $user->id] + $data);

        $complaint->user->notify(new ComplaintStatusChanged(
            $complaint, "Your complaint is now {$complaint->status_label}: {$data['remarks']}"
        ));

        return $complaint->fresh();
    }

    public function analytics(AnalyticsService $analytics)
    {
        return response()->json($analytics->all());
    }
}
