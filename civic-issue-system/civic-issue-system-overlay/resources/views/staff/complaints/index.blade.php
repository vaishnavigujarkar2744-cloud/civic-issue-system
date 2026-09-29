@extends('layouts.app')
@section('title', 'Assigned Complaints')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0">Complaints assigned to me</h3>
    <form method="GET">
        <select name="status" class="form-select" onchange="this.form.submit()">
            <option value="">All statuses</option>
            @foreach(\App\Models\Complaint::STATUS_LABELS as $k => $l)<option value="{{ $k }}" @selected(request('status') === $k)>{{ $l }}</option>@endforeach
        </select>
    </form>
</div>
<div class="card"><div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
        <thead class="table-light"><tr><th>ID</th><th>Title</th><th>Address</th><th>Priority</th><th>Status</th><th>Assigned</th><th></th></tr></thead>
        <tbody>
        @forelse($complaints as $c)
            <tr>
                <td>#{{ $c->id }}</td>
                <td>{{ $c->title }}</td>
                <td>{{ \Illuminate\Support\Str::limit($c->address, 30) }}</td>
                <td><span class="badge bg-{{ ['high'=>'danger','medium'=>'warning','low'=>'secondary'][$c->priority] }} text-capitalize">{{ $c->priority }}</span></td>
                <td><span class="badge bg-{{ $c->status_color }}">{{ $c->status_label }}</span></td>
                <td>{{ $c->updated_at->diffForHumans() }}</td>
                <td><a href="{{ route('staff.complaints.show', $c) }}" class="btn btn-sm btn-outline-primary">Open</a></td>
            </tr>
        @empty
            <tr><td colspan="7" class="text-center text-muted py-4">Nothing assigned to you yet.</td></tr>
        @endforelse
        </tbody>
    </table>
</div></div>
<div class="mt-3">{{ $complaints->links() }}</div>
@endsection
