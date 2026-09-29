@extends('layouts.app')
@section('title', 'Complaint #'.$complaint->id)
@section('content')
<a href="{{ route('admin.complaints.index') }}" class="btn btn-link px-0 mb-2">&larr; Back to all complaints</a>
<div class="row">
    <div class="col-lg-8">
        @include('partials.details')
        <p class="text-muted small">Reported by {{ $complaint->user->name }} ({{ $complaint->user->email }})</p>
    </div>
    <div class="col-lg-4">
        <div class="card mb-3"><div class="card-body">
            <h6 class="mb-3">Assign complaint</h6>
            <form method="POST" action="{{ route('admin.complaints.assign', $complaint) }}">
                @csrf
                <div class="mb-3">
                    <label class="form-label">Department</label>
                    <select name="department_id" id="department" class="form-select" required>
                        <option value="">Select department</option>
                        @foreach($departments as $d)
                            <option value="{{ $d->id }}" @selected($complaint->department_id === $d->id)>{{ $d->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Staff member</label>
                    <select name="assigned_to" id="staff" class="form-select" required>
                        <option value="">Select department first</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Priority</label>
                    <select name="priority" class="form-select">
                        @foreach(['low','medium','high'] as $p)<option value="{{ $p }}" @selected($complaint->priority === $p)>{{ ucfirst($p) }}</option>@endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Remarks (optional)</label>
                    <textarea name="remarks" rows="2" class="form-control"></textarea>
                </div>
                <button class="btn btn-primary w-100">Assign</button>
            </form>
        </div></div>
        <div class="card"><div class="card-body">
            <h6 class="mb-3">History</h6>
            @include('partials.timeline')
        </div></div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const deptSel = document.getElementById('department');
    const staffSel = document.getElementById('staff');
    const currentStaff = "{{ $complaint->assigned_to }}";

    // AJAX: load staff of the chosen department.
    async function loadStaff(deptId, selected) {
        staffSel.innerHTML = '<option value="">Loading…</option>';
        if (!deptId) { staffSel.innerHTML = '<option value="">Select department first</option>'; return; }

        const res = await fetch("{{ url('admin/departments') }}/" + deptId + "/staff", { headers: { 'Accept': 'application/json' } });
        const staff = await res.json();

        staffSel.innerHTML = '<option value="">Select staff member</option>';
        staff.forEach(s => {
            const opt = document.createElement('option');
            opt.value = s.id;
            opt.textContent = s.name;
            if (String(s.id) === String(selected)) opt.selected = true;
            staffSel.appendChild(opt);
        });
    }

    deptSel.addEventListener('change', () => loadStaff(deptSel.value, null));
    if (deptSel.value) loadStaff(deptSel.value, currentStaff);
</script>
@endpush
