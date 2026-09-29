@extends('layouts.app')
@section('title', 'Complaint #'.$complaint->id)
@section('content')
<a href="{{ route('staff.complaints.index') }}" class="btn btn-link px-0 mb-2">&larr; Back to assigned complaints</a>
<div class="row">
    <div class="col-lg-8">@include('partials.details')</div>
    <div class="col-lg-4">
        <div class="card mb-3"><div class="card-body">
            <h6 class="mb-3">Update status</h6>
            <form method="POST" action="{{ route('staff.complaints.status', $complaint) }}">
                @csrf @method('PATCH')
                <div class="mb-3">
                    <label class="form-label">New status</label>
                    <select name="status" class="form-select" required>
                        <option value="in_progress">In Progress</option>
                        <option value="resolved">Resolved</option>
                        <option value="rejected">Rejected</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Remarks (visible to citizen)</label>
                    <textarea name="remarks" rows="3" class="form-control" required></textarea>
                </div>
                <button class="btn btn-success w-100">Update</button>
            </form>
        </div></div>
        <div class="card"><div class="card-body">
            <h6 class="mb-3">History</h6>
            @include('partials.timeline')
        </div></div>
    </div>
</div>
@endsection
