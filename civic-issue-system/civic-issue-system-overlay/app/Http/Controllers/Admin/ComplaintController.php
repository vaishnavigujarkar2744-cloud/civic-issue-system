<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Complaint;
use App\Models\Department;
use App\Models\User;
use App\Notifications\ComplaintStatusChanged;
use Illuminate\Http\Request;

class ComplaintController extends Controller
{
    public function index(Request $request)
    {
        $complaints = Complaint::with('user', 'department', 'assignee')
            ->when($request->status, fn ($q, $s) => $q->where('status', $s))
            ->when($request->department_id, fn ($q, $d) => $q->where('department_id', $d))
            ->when($request->priority, fn ($q, $p) => $q->where('priority', $p))
            ->when($request->search, fn ($q, $s) => $q->where(
                fn ($w) => $w->where('title', 'like', "%{$s}%")->orWhere('address', 'like', "%{$s}%")
            ))
            ->latest()->paginate(15)->withQueryString();

        return view('admin.complaints.index', [
            'complaints' => $complaints,
            'departments' => Department::orderBy('name')->get(),
        ]);
    }

    public function show(Complaint $complaint)
    {
        $complaint->load('user', 'department', 'assignee', 'updates.user');

        return view('admin.complaints.show', [
            'complaint' => $complaint,
            'departments' => Department::orderBy('name')->get(),
        ]);
    }

    public function assign(Request $request, Complaint $complaint)
    {
        $data = $request->validate([
            'department_id' => 'required|exists:departments,id',
            'assigned_to' => 'required|exists:users,id',
            'priority' => 'required|in:low,medium,high',
            'remarks' => 'nullable|string|max:500',
        ]);

        // The staff member must belong to the chosen department.
        $staff = User::where('id', $data['assigned_to'])
            ->where('role', 'staff')
            ->where('department_id', $data['department_id'])
            ->firstOrFail();

        $complaint->update([
            'department_id' => $data['department_id'],
            'assigned_to' => $staff->id,
            'priority' => $data['priority'],
            'status' => 'assigned',
        ]);

        $complaint->updates()->create([
            'user_id' => $request->user()->id,
            'status' => 'assigned',
            'remarks' => $data['remarks'] ?: "Assigned to {$staff->name} ({$complaint->department->name}).",
        ]);

        $complaint->user->notify(new ComplaintStatusChanged($complaint, 'Your complaint has been assigned to a department.'));
        $staff->notify(new ComplaintStatusChanged($complaint, "A new complaint has been assigned to you: {$complaint->title}"));

        return back()->with('success', 'Complaint assigned successfully.');
    }

    /** AJAX: staff list for the dependent department -> staff dropdown. */
    public function staffByDepartment(Department $department)
    {
        return response()->json(
            $department->staff()->orderBy('name')->get(['id', 'name'])
        );
    }
}
