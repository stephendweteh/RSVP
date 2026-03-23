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
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Throwable;

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
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:50'],
            'email' => ['required', 'email', 'max:255'],
            'attendance' => ['required', 'in:attending,not_attending'],
            'message' => ['nullable', 'string', 'max:5000'],
        ]);

        if ($validated['attendance'] === 'attending' && Rsvp::isFullyBooked()) {
            throw ValidationException::withMessages([
                'rsvp' => ['RSVP fully booked. We are not accepting new submissions.'],
            ]);
        }

        $isNotAttending = $validated['attendance'] === 'not_attending';

        $rsvp = Rsvp::query()->create([
            ...$validated,
            'guests_count' => 1,
            'status' => $isNotAttending ? Rsvp::STATUS_NOT_ATTENDING : Rsvp::STATUS_PENDING,
            'table_number' => null,
            'check_in_token' => null,
            'checked_in_at' => null,
        ]);

        $this->sendMailSafely($rsvp->email, new RsvpSubmittedMail($rsvp), 'guest RSVP submission');

        foreach (AdminNotificationRecipients::notificationEmails() as $adminEmail) {
            $this->sendMailSafely($adminEmail, new RsvpSubmittedAdminMail($rsvp), 'admin RSVP submission notification');
        }

        RsvpSmsNotifier::guestSubmitted($rsvp);
        RsvpSmsNotifier::adminNewRsvp($rsvp);

        return redirect()
            ->route('rsvp.index')
            ->with('success', $isNotAttending
                ? 'Sorry you cannot attend. We have recorded your response.'
                : 'Your RSVP is awaiting approval.');
    }

    /**
     * Avoid failing RSVP submit when SMTP/network is temporarily broken.
     */
    private function sendMailSafely(?string $to, object $mailable, string $context): void
    {
        if (! filled($to)) {
            return;
        }

        try {
            Mail::to($to)->send($mailable);
        } catch (Throwable $e) {
            report($e);
            Log::warning('RSVP mail send failed; continuing request.', [
                'to' => $to,
                'context' => $context,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
