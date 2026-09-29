<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') - CivicConnect</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="{{ asset('css/custom.css') }}" rel="stylesheet">
    @stack('styles')
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
    <div class="container">
        <a class="navbar-brand" href="{{ route('dashboard') }}"><i class="bi bi-buildings"></i> CivicConnect</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#nav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="nav">
            <ul class="navbar-nav me-auto">
                @auth
                    @if(auth()->user()->isCitizen())
                        <li class="nav-item"><a class="nav-link" href="{{ route('citizen.complaints.index') }}">My Complaints</a></li>
                        <li class="nav-item"><a class="nav-link" href="{{ route('citizen.complaints.create') }}">Report Issue</a></li>
                    @elseif(auth()->user()->isAdmin())
                        <li class="nav-item"><a class="nav-link" href="{{ route('admin.dashboard') }}">Analytics</a></li>
                        <li class="nav-item"><a class="nav-link" href="{{ route('admin.complaints.index') }}">All Complaints</a></li>
                    @else
                        <li class="nav-item"><a class="nav-link" href="{{ route('staff.complaints.index') }}">Assigned to Me</a></li>
                    @endif
                @endauth
            </ul>
            <ul class="navbar-nav align-items-lg-center">
                @auth
                    <li class="nav-item dropdown me-lg-3">
                        <a class="nav-link position-relative" href="#" data-bs-toggle="dropdown" data-bs-auto-close="outside">
                            <i class="bi bi-bell fs-5"></i>
                            <span id="notif-badge" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger d-none">0</span>
                        </a>
                        <div class="dropdown-menu dropdown-menu-end p-0" style="width: 320px;">
                            <div class="d-flex justify-content-between align-items-center px-3 py-2 border-bottom">
                                <strong class="small">Notifications</strong>
                                <form method="POST" action="{{ route('notifications.readAll') }}">@csrf
                                    <button class="btn btn-link btn-sm p-0">Mark all read</button>
                                </form>
                            </div>
                            <div id="notif-list" style="max-height: 320px; overflow-y: auto;"></div>
                        </div>
                    </li>
                    <li class="nav-item"><span class="navbar-text me-3">{{ auth()->user()->name }} <span class="badge bg-secondary text-uppercase">{{ auth()->user()->role }}</span></span></li>
                    <li class="nav-item">
                        <form method="POST" action="{{ route('logout') }}">@csrf
                            <button class="btn btn-outline-light btn-sm">Logout</button>
                        </form>
                    </li>
                @else
                    <li class="nav-item"><a class="nav-link" href="{{ route('login') }}">Login</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('register') }}">Register</a></li>
                @endauth
            </ul>
        </div>
    </div>
</nav>

<main class="container pb-5">
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif
    @if($errors->any() && !request()->routeIs('login', 'register'))
        <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
    @endif
    @yield('content')
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
@auth
<script>
    // AJAX: poll unread notifications every 30 seconds.
    async function loadNotifications() {
        try {
            const res = await fetch("{{ route('notifications.unread') }}", { headers: { 'Accept': 'application/json' } });
            if (!res.ok) return;
            const data = await res.json();
            const badge = document.getElementById('notif-badge');
            const list = document.getElementById('notif-list');

            badge.textContent = data.count;
            badge.classList.toggle('d-none', data.count === 0);
            list.innerHTML = '';

            if (!data.items.length) {
                list.innerHTML = '<div class="p-3 text-muted small">No new notifications</div>';
                return;
            }
            data.items.forEach(n => {
                const a = document.createElement('a');
                a.href = n.url;
                a.className = 'dropdown-item small text-wrap border-bottom py-2';
                const title = document.createElement('div');
                title.className = 'fw-semibold';
                title.textContent = n.title;
                const msg = document.createElement('div');
                msg.textContent = n.message;
                const time = document.createElement('div');
                time.className = 'text-muted';
                time.textContent = n.time;
                a.append(title, msg, time);
                list.appendChild(a);
            });
        } catch (e) { /* ignore network errors */ }
    }
    loadNotifications();
    setInterval(loadNotifications, 30000);
</script>
@endauth
@stack('scripts')
</body>
</html>
