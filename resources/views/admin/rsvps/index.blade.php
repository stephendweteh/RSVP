@extends('layouts.admin')

@section('title', 'RSVPs — '.config('app.name'))

@section('content')
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <h1 class="h3 mb-0">Guest list (RSVPs)</h1>
        <div class="d-flex flex-wrap gap-2 align-items-center">
            <div class="btn-group" role="group" aria-label="Filter by status">
                <a href="{{ route('admin.rsvps.index') }}" class="btn btn-sm {{ $status === null ? 'btn-primary' : 'btn-outline-secondary' }}">All</a>
                <a href="{{ route('admin.rsvps.index', ['status' => 'pending']) }}" class="btn btn-sm {{ $status === 'pending' ? 'btn-warning' : 'btn-outline-secondary' }}">Pending</a>
                <a href="{{ route('admin.rsvps.index', ['status' => 'approved']) }}" class="btn btn-sm {{ $status === 'approved' ? 'btn-success' : 'btn-outline-secondary' }}">Approved</a>
                <a href="{{ route('admin.rsvps.index', ['status' => 'rejected']) }}" class="btn btn-sm {{ $status === 'rejected' ? 'btn-danger' : 'btn-outline-secondary' }}">Rejected</a>
                <a href="{{ route('admin.rsvps.index', ['status' => 'not_attending']) }}" class="btn btn-sm {{ $status === 'not_attending' ? 'btn-secondary' : 'btn-outline-secondary' }}">Not attending</a>
            </div>
            <a href="{{ route('admin.rsvps.export', array_filter(['status' => $status])) }}" class="btn btn-sm btn-outline-primary">Export CSV</a>
        </div>
    </div>

    <div class="table-responsive card border-0 shadow-sm">
        <table class="table table-hover align-middle mb-0 small">
            <thead class="table-light">
                <tr>
                    <th>Name</th>
                    <th>Phone</th>
                    <th>Email</th>
                    <th class="text-center">Guests</th>
                    <th>Attendance</th>
                    <th>Status</th>
                    <th class="text-center">Table</th>
                    <th class="text-center">Admitted</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($rsvps as $rsvp)
                    <tr>
                        <td class="fw-medium">{{ $rsvp->name }}</td>
                        <td>{{ $rsvp->phone }}</td>
                        <td>{{ $rsvp->email ?? '—' }}</td>
                        <td class="text-center">{{ $rsvp->guests_count }}</td>
                        <td>{{ $rsvp->attendance === 'attending' ? 'Attending' : 'Not attending' }}</td>
                        <td>
                            @if ($rsvp->status === 'pending')
                                <span class="badge text-bg-warning">Pending</span>
                            @elseif ($rsvp->status === 'approved')
                                <span class="badge text-bg-success">Approved</span>
                            @elseif ($rsvp->status === 'not_attending')
                                <span class="badge text-bg-secondary">Not attending</span>
                            @else
                                <span class="badge text-bg-danger">Rejected</span>
                            @endif
                        </td>
                        <td class="text-center">{{ $rsvp->table_number ?? '—' }}</td>
                        <td class="text-center">
                            @if ($rsvp->status === 'approved' && $rsvp->checked_in_at)
                                <span class="badge text-bg-info" title="{{ $rsvp->checked_in_at->format('M j, Y g:i A') }}">Yes</span>
                            @elseif ($rsvp->status === 'approved')
                                <span class="text-muted">—</span>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td class="text-end text-nowrap">
                            @if ($rsvp->status === 'not_attending')
                                <span class="text-muted">—</span>
                            @elseif ($rsvp->status === 'pending')
                                <form action="{{ route('admin.rsvps.approve', $rsvp->id) }}" method="post" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-success">Approve</button>
                                </form>
                                <form action="{{ route('admin.rsvps.reject', $rsvp->id) }}" method="post" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-danger">Reject</button>
                                </form>
                            @elseif ($rsvp->status === 'approved')
                                <form action="{{ route('admin.rsvps.reject', $rsvp->id) }}" method="post" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-danger">Reject</button>
                                </form>
                            @else
                                <form action="{{ route('admin.rsvps.approve', $rsvp->id) }}" method="post" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-success">Approve</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                    @if ($rsvp->message)
                        <tr class="border-top-0">
                            <td colspan="9" class="pt-0 text-muted fst-italic small">{{ $rsvp->message }}</td>
                        </tr>
                    @endif
                @empty
                    <tr>
                        <td colspan="9" class="text-center text-muted py-5">No RSVPs match this filter.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3">
        {{ $rsvps->links() }}
    </div>
@endsection
