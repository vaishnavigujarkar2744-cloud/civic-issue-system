<ul class="timeline mb-0">
    @foreach($complaint->updates as $u)
        <li>
            <span class="badge bg-{{ \App\Models\Complaint::STATUS_COLORS[$u->status] ?? 'secondary' }}">{{ \App\Models\Complaint::STATUS_LABELS[$u->status] ?? $u->status }}</span>
            <small class="text-muted ms-2">{{ $u->created_at->format('d M Y, h:i A') }} &middot; {{ $u->user->name }}</small>
            @if($u->remarks)<div class="small mt-1">{{ $u->remarks }}</div>@endif
        </li>
    @endforeach
</ul>
