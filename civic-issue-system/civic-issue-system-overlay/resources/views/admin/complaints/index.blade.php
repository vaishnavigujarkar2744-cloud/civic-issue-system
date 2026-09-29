@extends('layouts.app')
@section('title', 'All Complaints')
@section('content')
<h3 class="mb-3">All Complaints</h3>

<form method="GET" class="card card-body mb-3">
    <div class="row g-2">
        <div class="col-md-4"><input name="search" value="{{ request('search') }}" class="form-control" placeholder="Search title or address"></div>
        <div class="col-md-2">
            <select name="status" class="form-select">
                <option value="">All statuses</option>
                @foreach(\App\Models\Complaint::STATUS_LABELS as $k => $l)<option value="{{ $k }}" @selected(request('status') === $k)>{{ $l }}</option>@endforeach
            </select>
        </div>
        <div class="col-md-3">
            <select name="department_id" class="form-select">
                <option value="">All departments</option>
                @foreach($departments as $d)<option value="{{ $d->id }}" @selected(request('department_id') == $d->id)>{{ $d->name }}</option>@endforeach
            </select>
        </div>
        <div class="col-md-2">
            <select name="priority" class="form-select">
                <option value="">Any priority</option>
                @foreach(['high','medium','low'] as $p)<option value="{{ $p }}" @selected(request('priority') === $p)>{{ ucfirst($p) }}</option>@endforeach
            </select>
        </div>
        <div class="col-md-1 d-grid"><button class="btn btn-primary">Filter</button></div>
    </div>
</form>

<div class="card"><div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
        <thead class="table-light"><tr><th>ID</th><th>Title</th><th>Citizen</th><th>Department</th><th>Assigned to</th><th>Priority</th><th>Status</th><th>Date</th><th></th></tr></thead>
        <tbody>
        @forelse($complaints as $c)
            <tr>
                <td>#{{ $c->id }}</td>
                <td>{{ \Illuminate\Support\Str::limit($c->title, 40) }}</td>
                <td>{{ $c->user->name }}</td>
                <td>{{ $c->department->name ?? '—' }}</td>
                <td>{{ $c->assignee->name ?? '—' }}</td>
                <td class="text-capitalize">{{ $c->priority }}</td>
                <td><span class="badge bg-{{ $c->status_color }}">{{ $c->status_label }}</span></td>
                <td>{{ $c->created_at->format('d M Y') }}</td>
                <td><a href="{{ route('admin.complaints.show', $c) }}" class="btn btn-sm btn-outline-primary">Manage</a></td>
            </tr>
        @empty
            <tr><td colspan="9" class="text-center text-muted py-4">No complaints found.</td></tr>
        @endforelse
        </tbody>
    </table>
</div></div>
<div class="mt-3">{{ $complaints->links() }}</div>
@endsection
