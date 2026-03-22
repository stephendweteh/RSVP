@extends('layouts.admin')

@section('title', 'Dashboard — '.config('app.name'))

@section('content')
    <h1 class="h3 mb-4">Dashboard</h1>
    <div class="row g-3 mb-4">
        <div class="col-sm-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small">Pending</div>
                    <div class="display-6">{{ $counts['pending'] }}</div>
                    <a href="{{ route('admin.rsvps.index', ['status' => 'pending']) }}" class="stretched-link text-decoration-none small">Review</a>
                </div>
            </div>
        </div>
        <div class="col-sm-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small">Approved</div>
                    <div class="display-6">{{ $counts['approved'] }}</div>
                    <a href="{{ route('admin.rsvps.index', ['status' => 'approved']) }}" class="stretched-link text-decoration-none small">View list</a>
                </div>
            </div>
        </div>
        <div class="col-sm-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small">Rejected</div>
                    <div class="display-6">{{ $counts['rejected'] }}</div>
                    <a href="{{ route('admin.rsvps.index', ['status' => 'rejected']) }}" class="stretched-link text-decoration-none small">View list</a>
                </div>
            </div>
        </div>
    </div>
    <div class="text-center mt-4 pt-2">
        <a href="{{ route('admin.rsvps.index') }}" class="btn btn-primary rounded-pill px-5 py-2 shadow-sm">View all RSVPs</a>
    </div>
    <p class="text-muted small text-center mt-3 mb-0">
        Guest form: <a href="{{ route('rsvp.index') }}" target="_blank" rel="noopener">{{ url('/rsvp') }}</a>
        · <a href="{{ route('admin.settings.edit') }}">Settings</a> for email &amp; notifications
    </p>
@endsection
