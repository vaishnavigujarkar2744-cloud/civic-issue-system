@extends('layouts.app')
@section('title', 'Analytics')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0">City Analytics</h3>
    <small class="text-muted">Auto-refreshes every 30s &middot; <span id="updated">loading…</span></small>
</div>

<div class="row g-3 mb-4">
    @foreach([['total','Total','primary'],['pending','Pending','secondary'],['in_progress','In Progress','warning'],['resolved','Resolved','success'],['avg_resolution_hours','Avg. resolution (hrs)','info']] as [$k,$label,$c])
        <div class="col-6 col-md">
            <div class="card stat-card" style="border-left-color: var(--bs-{{ $c }});"><div class="card-body py-3">
                <div class="text-muted small">{{ $label }}</div>
                <div class="display-6" id="stat-{{ $k }}">–</div>
            </div></div>
        </div>
    @endforeach
</div>

<div class="row g-3">
    <div class="col-lg-7"><div class="card"><div class="card-body">
        <h6>Complaints by department</h6><div class="chart-box"><canvas id="deptChart"></canvas></div>
    </div></div></div>
    <div class="col-lg-5"><div class="card"><div class="card-body">
        <h6>Status distribution</h6><div class="chart-box"><canvas id="statusChart"></canvas></div>
    </div></div></div>
    <div class="col-lg-7"><div class="card"><div class="card-body">
        <h6>Monthly trend</h6><div class="chart-box"><canvas id="monthChart"></canvas></div>
    </div></div></div>
    <div class="col-lg-5"><div class="card"><div class="card-body">
        <h6>Department resolution rate</h6>
        <table class="table table-sm align-middle mb-0"><tbody id="dept-table"></tbody></table>
    </div></div></div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
<script>
    const charts = {};

    function draw(id, config) {
        if (charts[id]) { charts[id].data = config.data; charts[id].update(); return; }
        charts[id] = new Chart(document.getElementById(id), config);
    }

    async function loadAnalytics() {
        const res = await fetch("{{ route('admin.analytics') }}", { headers: { 'Accept': 'application/json' } });
        if (!res.ok) return;
        const d = await res.json();

        Object.entries(d.summary).forEach(([k, v]) => {
            const el = document.getElementById('stat-' + k);
            if (el) el.textContent = v;
        });

        draw('deptChart', {
            type: 'bar',
            data: {
                labels: d.by_department.map(x => x.department),
                datasets: [
                    { label: 'Total', data: d.by_department.map(x => x.total), backgroundColor: '#0d6efd' },
                    { label: 'Resolved', data: d.by_department.map(x => x.resolved), backgroundColor: '#198754' }
                ]
            },
            options: { responsive: true, maintainAspectRatio: false, scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } }
        });

        draw('statusChart', {
            type: 'doughnut',
            data: {
                labels: d.by_status.map(x => x.status),
                datasets: [{ data: d.by_status.map(x => x.count), backgroundColor: ['#6c757d', '#0dcaf0', '#ffc107', '#198754', '#dc3545'] }]
            },
            options: { responsive: true, maintainAspectRatio: false }
        });

        draw('monthChart', {
            type: 'line',
            data: {
                labels: d.monthly.map(x => x.month),
                datasets: [{ label: 'Complaints', data: d.monthly.map(x => x.count), borderColor: '#0d6efd', tension: .3, fill: false }]
            },
            options: { responsive: true, maintainAspectRatio: false, scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } }
        });

        const tbody = document.getElementById('dept-table');
        tbody.innerHTML = '';
        d.by_department.forEach(x => {
            const tr = document.createElement('tr');
            const name = document.createElement('td'); name.textContent = x.department;
            const bar = document.createElement('td'); bar.style.width = '45%';
            bar.innerHTML = '<div class="progress" style="height:8px"><div class="progress-bar bg-success"></div></div>';
            bar.querySelector('.progress-bar').style.width = x.resolution_rate + '%';
            const pct = document.createElement('td'); pct.className = 'text-end small'; pct.textContent = x.resolution_rate + '%';
            tr.append(name, bar, pct);
            tbody.appendChild(tr);
        });

        document.getElementById('updated').textContent = 'updated ' + new Date().toLocaleTimeString();
    }

    loadAnalytics();
    setInterval(loadAnalytics, 30000);
</script>
@endpush
