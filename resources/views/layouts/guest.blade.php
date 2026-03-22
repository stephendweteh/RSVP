<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name'))</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <style>
        :root {
            --rsvp-blueblack-deep: #05080f;
            --rsvp-blueblack-mid: #0c1524;
            --rsvp-blueblack-accent: #111f33;
            --rsvp-blue-cta: #2563eb;
            --rsvp-blue-cta-hover: #1d4ed8;
        }
        body {
            min-height: 100dvh;
            background: linear-gradient(165deg, var(--rsvp-blueblack-deep) 0%, var(--rsvp-blueblack-mid) 42%, var(--rsvp-blueblack-accent) 78%, #0a1628 100%);
            color: #cbd5e1;
        }
        .card-rsvp {
            border: 1px solid rgba(59, 130, 246, 0.14);
            border-radius: 1rem;
            background: #f8fafc;
            color: #0f172a;
            box-shadow:
                0 0.5rem 2rem rgba(0, 0, 0, 0.35),
                0 0 1.5rem rgba(37, 99, 235, 0.06);
        }
        .btn-primary {
            background-color: var(--rsvp-blue-cta);
            border-color: #1d4ed8;
        }
        .btn-primary:hover, .btn-primary:focus {
            background-color: var(--rsvp-blue-cta-hover);
            border-color: #1e40af;
        }
        .btn-primary:focus {
            box-shadow: 0 0 0 0.2rem rgba(37, 99, 235, 0.35);
        }
    </style>
    @stack('styles')
</head>
<body class="d-flex flex-column">
    @php($guestLayout = trim($__env->yieldContent('guest_layout', '')))
    <main @class([
        'flex-grow-1',
        'd-flex',
        'align-items-stretch' => $guestLayout === 'fluid',
        'align-items-center py-4 px-3' => $guestLayout !== 'fluid',
    ])>
        @if ($guestLayout === 'fluid')
            @yield('content')
        @else
            <div class="container px-3" style="max-width: @yield('container_max', '28rem'); margin-left: auto; margin-right: auto; width: 100%;">
                @yield('content')
            </div>
        @endif
    </main>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    @stack('scripts')
</body>
</html>
