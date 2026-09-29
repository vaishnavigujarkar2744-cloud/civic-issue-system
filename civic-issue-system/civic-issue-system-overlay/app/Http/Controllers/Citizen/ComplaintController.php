<?php

namespace App\Http\Controllers\Citizen;

use App\Http\Controllers\Controller;
use App\Models\Complaint;
use App\Models\User;
use App\Notifications\ComplaintStatusChanged;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ComplaintController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $counts = $user->complaints()->selectRaw('status, COUNT(*) as c')->groupBy('status')->pluck('c', 'status');

        $complaints = $user->complaints()
            ->with('department')
            ->when($request->status, fn ($q, $s) => $q->where('status', $s))
            ->latest()->paginate(10)->withQueryString();

        return view('citizen.complaints.index', compact('complaints', 'counts'));
    }

    public function create()
    {
        return view('citizen.complaints.create', ['categories' => Complaint::CATEGORIES]);
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
            'user_id' => $request->user()->id,
            'status' => 'pending',
            'remarks' => 'Complaint submitted.',
        ]);

        User::where('role', 'admin')->get()->each(
            fn ($admin) => $admin->notify(new ComplaintStatusChanged($complaint, "New complaint submitted: {$complaint->title}"))
        );

        return redirect()->route('citizen.complaints.show', $complaint)
            ->with('success', 'Your complaint has been submitted. Complaint ID: #'.$complaint->id);
    }

    public function show(Request $request, Complaint $complaint)
    {
        abort_unless($complaint->user_id === $request->user()->id, 403);

        $complaint->load('updates.user', 'department', 'assignee');

        return view('citizen.complaints.show', compact('complaint'));
    }
}
