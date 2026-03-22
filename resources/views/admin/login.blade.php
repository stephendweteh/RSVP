@extends('layouts.guest')

@section('title', 'Admin login — '.config('app.name'))

@section('content')
    <div class="card card-rsvp">
        <div class="card-body p-4">
            <h1 class="h5 text-center mb-4">Admin sign in</h1>

            @if ($errors->any())
                <div class="alert alert-danger small" role="alert">
                    {{ $errors->first() }}
                </div>
            @endif

            <form action="{{ route('admin.login') }}" method="post">
                @csrf
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="email" name="email" value="{{ old('email') }}" required autocomplete="username" autofocus>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" class="form-control" id="password" name="password" required autocomplete="current-password">
                </div>
                <div class="form-check mb-4">
                    <input class="form-check-input" type="checkbox" name="remember" id="remember" value="1">
                    <label class="form-check-label" for="remember">Remember me</label>
                </div>
                <button type="submit" class="btn btn-primary w-100 py-2">Sign in</button>
            </form>
        </div>
    </div>
@endsection
