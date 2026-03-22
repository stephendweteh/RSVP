<?php

namespace App\Http\Controllers;

use App\Mail\RsvpSubmittedAdminMail;
use App\Mail\RsvpSubmittedMail;
use App\Models\Rsvp;
use App\Models\Setting;
use App\Models\SliderImage;
use App\Services\AdminNotificationRecipients;
use App\Services\RsvpSmsNotifier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RsvpController extends Controller
{
    public function index(): View
    {
        $sliderImages = SliderImage::query()->orderBy('sort_order')->orderBy('id')->get();
        $rsvpTitle = Setting::get('rsvp_page_title', 'Wedding RSVP');
        $rsvpSubtitle = Setting::get('rsvp_page_subtitle', 'We would love to hear from you.');
        $rsvpFullyBooked = Rsvp::isFullyBooked();

        return view('rsvp.index', compact('sliderImages', 'rsvpTitle', 'rsvpSubtitle', 'rsvpFullyBooked'));
    }

    public function store(Request $request): RedirectResponse
    {
        if (Rsvp::isFullyBooked()) {
            throw ValidationException::withMessages([
                'rsvp' => ['RSVP fully booked. We are not accepting new submissions.'],
            ]);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:50'],
            'email' => ['required', 'email', 'max:255'],
            'attendance' => ['required', 'in:attending,not_attending'],
            'message' => ['nullable', 'string', 'max:5000'],
        ]);

        $rsvp = Rsvp::query()->create([
            ...$validated,
            'guests_count' => 1,
            'status' => Rsvp::STATUS_PENDING,
        ]);

        Mail::to($rsvp->email)->send(new RsvpSubmittedMail($rsvp));

        foreach (AdminNotificationRecipients::notificationEmails() as $adminEmail) {
            Mail::to($adminEmail)->send(new RsvpSubmittedAdminMail($rsvp));
        }

        RsvpSmsNotifier::guestSubmitted($rsvp);
        RsvpSmsNotifier::adminNewRsvp($rsvp);

        return redirect()
            ->route('rsvp.index')
            ->with('success', 'Your RSVP is awaiting approval.');
    }
}
