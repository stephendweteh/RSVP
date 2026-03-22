@extends('layouts.guest')

@section('guest_layout', 'fluid')

@section('title', ($rsvpTitle ?? 'RSVP').' — '.config('app.name'))

@push('styles')
    <style>
        .rsvp-split-row { min-height: 100dvh; }
        .rsvp-split-slider-col { min-height: 280px; }
        #rsvpCarousel.rsvp-carousel {
            height: 280px;
            border-radius: 1rem;
            overflow: hidden;
            box-shadow: 0 0.5rem 2rem rgba(0, 0, 0, 0.35);
            border: 1px solid rgba(59, 130, 246, 0.14);
            background: #0f172a;
        }
        @media (min-width: 992px) {
            .rsvp-split-slider-col {
                min-height: 100dvh;
            }
            #rsvpCarousel.rsvp-carousel {
                height: 100%;
                min-height: 100dvh;
                border-radius: 0 1rem 1rem 0;
                border-left: none;
                box-shadow: 0.75rem 0 2rem rgba(0, 0, 0, 0.4);
            }
        }
        .rsvp-carousel .carousel-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .rsvp-carousel .carousel-inner,
        .rsvp-carousel .carousel-item { height: 100%; }
        .rsvp-split-form-inner { max-width: 28rem; }
    </style>
@endpush

@section('content')
    <div class="container-fluid px-0">
        <div class="row g-0 align-items-stretch rsvp-split-row">
            <div class="col-12 col-lg-6 order-1 p-0 rsvp-split-slider-col d-flex">
                <div id="rsvpCarousel" class="carousel slide rsvp-carousel w-100 flex-grow-1" data-bs-ride="carousel" data-bs-interval="5000">
                    @if ($sliderImages->isNotEmpty())
                        <div class="carousel-indicators">
                            @foreach ($sliderImages as $img)
                                <button type="button" data-bs-target="#rsvpCarousel" data-bs-slide-to="{{ $loop->index }}" @class(['active' => $loop->first]) aria-current="{{ $loop->first ? 'true' : 'false' }}" aria-label="Slide {{ $loop->iteration }}"></button>
                            @endforeach
                        </div>
                        <div class="carousel-inner">
                            @foreach ($sliderImages as $img)
                                <div class="carousel-item @if($loop->first) active @endif">
                                    <img src="{{ $img->url() }}" class="d-block w-100" alt="">
                                </div>
                            @endforeach
                        </div>
                        <button class="carousel-control-prev" type="button" data-bs-target="#rsvpCarousel" data-bs-slide="prev">
                            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                            <span class="visually-hidden">Previous</span>
                        </button>
                        <button class="carousel-control-next" type="button" data-bs-target="#rsvpCarousel" data-bs-slide="next">
                            <span class="carousel-control-next-icon" aria-hidden="true"></span>
                            <span class="visually-hidden">Next</span>
                        </button>
                    @else
                        <div class="carousel-inner h-100">
                            <div class="carousel-item active h-100 d-flex align-items-center justify-content-center bg-secondary bg-opacity-10">
                                <div class="text-center text-white-50 px-4 py-5">
                                    <p class="mb-1 small text-uppercase tracking-wide">Welcome</p>
                                    <p class="mb-0 small">Photos for this slider can be added in the admin <strong>Slider</strong> tab.</p>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
            <div class="col-12 col-lg-6 order-2 d-flex align-items-center justify-content-center py-4 py-lg-5 px-3 px-lg-4">
                <div class="w-100 rsvp-split-form-inner">
                    <div class="card card-rsvp h-100">
                        <div class="card-body p-4">
                            <h1 class="h4 text-center {{ filled($rsvpSubtitle) ? 'mb-1' : 'mb-4' }}">{{ $rsvpTitle }}</h1>
                            @if (filled($rsvpSubtitle))
                                <p class="text-muted text-center small mb-4">{{ $rsvpSubtitle }}</p>
                            @endif

                            @if (session('success'))
                                <div class="alert alert-success" role="alert">{{ session('success') }}</div>
                            @endif

                            @if ($errors->any())
                                <div class="alert alert-danger" role="alert">
                                    <ul class="mb-0 ps-3 small">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            @if ($rsvpFullyBooked)
                                <div class="alert alert-secondary text-center mb-0 py-4" role="status">
                                    <p class="fw-semibold mb-1">RSVP fully booked</p>
                                    <p class="small text-muted mb-0">We have reached our guest capacity and are no longer accepting new RSVPs. Thank you for your interest.</p>
                                </div>
                            @else
                                <form action="{{ route('rsvp.store') }}" method="post" novalidate>
                                    @csrf
                                    <div class="mb-3">
                                        <label for="name" class="form-label">Your name</label>
                                        <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" required autocomplete="name">
                                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="mb-3">
                                        <label for="phone" class="form-label">Phone</label>
                                        <input type="tel" class="form-control @error('phone') is-invalid @enderror" id="phone" name="phone" value="{{ old('phone') }}" required autocomplete="tel">
                                        @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="mb-3">
                                        <label for="email" class="form-label">Email</label>
                                        <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email') }}" required autocomplete="email">
                                        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label d-block">Will you attend?</label>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input @error('attendance') is-invalid @enderror" type="radio" name="attendance" id="attending" value="attending" @checked(old('attendance') === 'attending') required>
                                            <label class="form-check-label" for="attending">Attending</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input @error('attendance') is-invalid @enderror" type="radio" name="attendance" id="not_attending" value="not_attending" @checked(old('attendance') === 'not_attending')>
                                            <label class="form-check-label" for="not_attending">Not attending</label>
                                        </div>
                                        @error('attendance')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="mb-4">
                                        <label for="message" class="form-label">Message <span class="text-muted">(optional)</span></label>
                                        <textarea class="form-control @error('message') is-invalid @enderror" id="message" name="message" rows="3">{{ old('message') }}</textarea>
                                        @error('message')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                    <button type="submit" class="btn btn-primary w-100 py-2">Submit RSVP</button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
