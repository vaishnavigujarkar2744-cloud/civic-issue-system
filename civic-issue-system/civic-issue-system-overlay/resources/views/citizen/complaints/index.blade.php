@extends('layouts.app')
@section('title', 'My Complaints')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0">My Complaints</h3>
    <a href="{{ route('citizen.complaints.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Report an Issue</a>
</div>

<div class="row g-3 mb-4">
    @foreach(\App\Models\Complaint::STATUS_LABELS as $key => $label)
        <div class="col-6 col-md">
            <a href="{{ route('citizen.complaints.index', ['status' => $key]) }}" class="text-decoration-none">
                <div class="card stat-card"><div class="card-body py-3">
                    <div class="text-muted small">{{ $label }}</div>
                    <div class="display-6 text-dark">{{ $counts[$key] ?? 0 }}</div>
                </div></div>
            </a>
        </div>
    @endforeach
</div>

<div class="card"><div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
        <thead class="table-light"><tr><th>ID</th><th>Title</th><th>Category</th><th>Department</th><th>Status</th><th>Date</th><th></th></tr></thead>
        <tbody>
        @forelse($complaints as $c)
            <tr>
                <td>#{{ $c->id }}</td>
                <td>{{ $c->title }}</td>
                <td>{{ $c->category }}</td>
                <td>{{ $c->department->name ?? '—' }}</td>
                <td><span class="badge bg-{{ $c->status_color }}">{{ $c->status_label }}</span></td>
                <td>{{ $c->created_at->format('d M Y') }}</td>
                <td><a href="{{ route('citizen.complaints.show', $c) }}" class="btn btn-sm btn-outline-primary">Track</a></td>
            </tr>
        @empty
            <tr><td colspan="7" class="text-center text-muted py-4">No complaints yet.</td></tr>
        @endforelse
        </tbody>
    </table>
</div></div>
<div class="mt-3">{{ $complaints->links() }}</div>
@endsection
