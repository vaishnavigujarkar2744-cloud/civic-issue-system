<div class="card mb-4">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-start mb-2">
            <div>
                <h4 class="mb-1">#{{ $complaint->id }} &middot; {{ $complaint->title }}</h4>
                <span class="badge bg-{{ $complaint->status_color }}">{{ $complaint->status_label }}</span>
                <span class="badge bg-light text-dark border text-capitalize">Priority: {{ $complaint->priority }}</span>
                <span class="badge bg-light text-dark border">{{ $complaint->category }}</span>
            </div>
            <small class="text-muted">{{ $complaint->created_at->format('d M Y, h:i A') }}</small>
        </div>
        <p class="mt-3">{{ $complaint->description }}</p>
        <p class="mb-1"><i class="bi bi-geo-alt"></i> {{ $complaint->address }}
            @if($complaint->latitude && $complaint->longitude)
                <a target="_blank" rel="noopener" href="https://www.google.com/maps?q={{ $complaint->latitude }},{{ $complaint->longitude }}">(view on map)</a>
            @endif
        </p>
        <p class="mb-1"><i class="bi bi-diagram-3"></i> Department: {{ $complaint->department->name ?? 'Not assigned yet' }}</p>
        <p class="mb-3"><i class="bi bi-person-badge"></i> Assigned to: {{ $complaint->assignee->name ?? '—' }}</p>
        @if($complaint->image_url)
            <img src="{{ $complaint->image_url }}" alt="Complaint photo" class="complaint-img">
        @endif
    </div>
</div>
