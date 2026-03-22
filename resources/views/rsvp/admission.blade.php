@extends('layouts.guest')

@section('title', ($valid ? 'You\'re on the list' : 'Check-in').' — '.config('app.name'))

@section('content')
    <div class="card card-rsvp p-4 p-md-5 text-center">
        @if ($valid && $rsvp)
            <div class="text-success mb-3" style="font-size: 2.5rem;" aria-hidden="true">✓</div>
            <h1 class="h4 mb-2">You’re on the guest list</h1>
            <p class="mb-1 fw-medium">{{ $rsvp->name }}</p>
            @if (filled($rsvp->table_number))
                <p class="text-muted small mb-0">Table {{ $rsvp->table_number }}</p>
            @endif
            <p class="text-muted small mt-3 mb-0">Show this screen or your approval email at the entrance if asked.</p>
        @else
            <h1 class="h5 mb-2">Check-in link not valid</h1>
            <p class="text-muted small mb-0">This link may be wrong or the RSVP is no longer approved. Contact the hosts if you need help.</p>
        @endif
    </div>
@endsection
