@extends('layouts.app')
@section('title', 'Complaint #'.$complaint->id)
@section('content')
<a href="{{ route('citizen.complaints.index') }}" class="btn btn-link px-0 mb-2">&larr; Back to my complaints</a>
<div class="row">
    <div class="col-lg-8">@include('partials.details')</div>
    <div class="col-lg-4">
        <div class="card"><div class="card-body">
            <h6 class="mb-3">Progress</h6>
            @include('partials.timeline')
        </div></div>
    </div>
</div>
@endsection
