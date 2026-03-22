<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin — '.config('app.name'))</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <style>
        :root {
            --bs-primary: #2563eb;
            --bs-primary-rgb: 37, 99, 235;
            --bs-link-color: #1d4ed8;
            --bs-link-hover-color: #1e40af;
        }
        .navbar-blueblack {
            background: linear-gradient(135deg, #050810 0%, #0c1524 45%, #0f1b2e 100%) !important;
            box-shadow: 0 1px 0 rgba(59, 130, 246, 0.14);
        }
        .nav-account-link:hover { background-color: rgba(255, 255, 255, 0.08); }
        body.admin-surface { background: linear-gradient(180deg, #eef2f9 0%, #e2e8f0 100%); }
    </style>
    @stack('styles')
</head>
<body class="admin-surface min-vh-100">
    <nav class="navbar navbar-expand-lg navbar-dark navbar-blueblack">
        <div class="container-fluid">
            <a class="navbar-brand" href="{{ route('admin.dashboard') }}">RSVP Admin</a>
            <div class="navbar-nav ms-auto flex-row flex-wrap gap-2 align-items-center">
                <a class="nav-link text-white-50 small py-2" href="{{ route('admin.rsvps.index') }}">All RSVPs</a>
                <a class="nav-link text-white-50 small py-2" href="{{ route('admin.rsvp-title.edit') }}">RSVP TITLE</a>
                <a class="nav-link text-white-50 small py-2" href="{{ route('admin.slider.index') }}">Slider</a>
                @if (auth()->user()->canAccessSettings())
                    <a class="nav-link text-white-50 small py-2" href="{{ route('admin.settings.edit') }}">Settings</a>
                @endif
                @if (auth()->user()->isAdministrator())
                    <a class="nav-link text-white-50 small py-2" href="{{ route('admin.users.index') }}">Users</a>
                @endif
                @auth
                    <div class="vr text-white-50 d-none d-md-block align-self-stretch my-2 opacity-50"></div>
                    <a href="{{ route('admin.users.show', auth()->user()) }}" class="d-flex align-items-center gap-2 text-decoration-none rounded-pill px-2 py-1 nav-account-link" title="Your profile">
                        @include('admin.users.partials.avatar-display', [
                            'user' => auth()->user(),
                            'size' => 36,
                            'class' => 'border border-light border-opacity-25 flex-shrink-0',
                        ])
                        <span class="small fw-medium text-white text-truncate" style="max-width: 10rem;">{{ auth()->user()->name }}</span>
                    </a>
                @endauth
                <form action="{{ route('admin.logout') }}" method="post" class="d-inline ms-md-1">
                    @csrf
                    <button type="submit" class="btn btn-outline-light btn-sm">Log out</button>
                </form>
            </div>
        </div>
    </nav>
    <main class="container py-4">
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <ul class="mb-0 ps-3 small">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        @yield('content')
    </main>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    @stack('scripts')
</body>
</html>
