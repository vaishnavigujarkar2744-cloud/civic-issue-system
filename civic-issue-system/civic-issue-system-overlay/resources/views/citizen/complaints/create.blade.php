@extends('layouts.app')
@section('title', 'Report an Issue')
@section('content')
<div class="row justify-content-center"><div class="col-lg-8">
<div class="card"><div class="card-body p-4">
    <h4 class="mb-3">Report a civic issue</h4>
    <form method="POST" action="{{ route('citizen.complaints.store') }}" enctype="multipart/form-data">
        @csrf
        <div class="mb-3">
            <label class="form-label">Title</label>
            <input name="title" value="{{ old('title') }}" class="form-control @error('title') is-invalid @enderror" required maxlength="150">
            @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="mb-3">
            <label class="form-label">Category</label>
            <select name="category" class="form-select @error('category') is-invalid @enderror" required>
                <option value="">Select category</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat }}" @selected(old('category') === $cat)>{{ $cat }}</option>
                @endforeach
            </select>
        </div>
        <div class="mb-3">
            <label class="form-label">Description</label>
            <textarea name="description" rows="4" class="form-control @error('description') is-invalid @enderror" required>{{ old('description') }}</textarea>
        </div>
        <div class="mb-3">
            <label class="form-label">Address / Landmark</label>
            <div class="input-group">
                <input name="address" id="address" value="{{ old('address') }}" class="form-control @error('address') is-invalid @enderror" required>
                <button type="button" class="btn btn-outline-secondary" id="btn-locate"><i class="bi bi-crosshair"></i> Use my location</button>
            </div>
            <input type="hidden" name="latitude" id="latitude" value="{{ old('latitude') }}">
            <input type="hidden" name="longitude" id="longitude" value="{{ old('longitude') }}">
            <div class="form-text" id="loc-status"></div>
        </div>
        <div class="mb-3">
            <label class="form-label">Photo (optional, max 4 MB)</label>
            <input type="file" name="image" id="image" accept="image/*" class="form-control @error('image') is-invalid @enderror">
            @error('image')<div class="invalid-feedback">{{ $message }}</div>@enderror
            <img id="preview" class="img-thumbnail mt-2 d-none" style="max-height:180px">
        </div>
        <button class="btn btn-primary">Submit Complaint</button>
        <a href="{{ route('citizen.complaints.index') }}" class="btn btn-link">Cancel</a>
    </form>
</div></div>
</div></div>
@endsection

@push('scripts')
<script>
    document.getElementById('image').addEventListener('change', e => {
        const file = e.target.files[0];
        const img = document.getElementById('preview');
        if (!file) { img.classList.add('d-none'); return; }
        img.src = URL.createObjectURL(file);
        img.classList.remove('d-none');
    });

    document.getElementById('btn-locate').addEventListener('click', () => {
        const status = document.getElementById('loc-status');
        if (!navigator.geolocation) { status.textContent = 'Geolocation is not supported by your browser.'; return; }
        status.textContent = 'Getting location...';
        navigator.geolocation.getCurrentPosition(pos => {
            document.getElementById('latitude').value = pos.coords.latitude.toFixed(7);
            document.getElementById('longitude').value = pos.coords.longitude.toFixed(7);
            status.textContent = 'Location captured (' + pos.coords.latitude.toFixed(4) + ', ' + pos.coords.longitude.toFixed(4) + '). Please also type the address/landmark.';
        }, () => { status.textContent = 'Could not get your location.'; });
    });
</script>
@endpush
