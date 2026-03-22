<?php

namespace App\Http\Controllers;

use App\Models\MailTemplate;
use App\Models\Rsvp;
use App\Models\Setting;
use App\Services\ArkeselSmsService;
use App\Services\DatabaseMailConfig;
use App\Services\RsvpSmsNotifier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminSettingsController extends Controller
{
    public function edit(): View
    {
        $mailSmtpActive = filled(trim(Setting::get('mail_smtp_host')));
        $rsvpCount = Rsvp::query()->count();
        $mailTemplates = MailTemplate::query()->orderBy('sort_order')->orderBy('id')->get();
        $smsArkeselActive = ArkeselSmsService::isConfigured();

        return view('admin.settings.edit', compact('mailSmtpActive', 'rsvpCount', 'mailTemplates', 'smsArkeselActive'));
    }

    public function update(Request $request): RedirectResponse
    {
        $rules = [
            'smtp_host' => ['nullable', 'string', 'max:255'],
            'smtp_port' => ['nullable', 'integer', 'min:1', 'max:65535'],
            'smtp_username' => ['nullable', 'string', 'max:255'],
            'smtp_password' => ['nullable', 'string', 'max:255'],
            'smtp_encryption' => ['nullable', 'in:tls,ssl,none'],
            'mail_from_address' => ['nullable', 'email', 'max:255'],
            'mail_from_name' => ['nullable', 'string', 'max:255'],
            'admin_notification_email' => ['nullable', 'email', 'max:255'],
        ];

        if (filled(trim((string) $request->input('smtp_host')))) {
            $rules['smtp_port'] = ['required', 'integer', 'min:1', 'max:65535'];
            $rules['mail_from_address'] = ['required', 'email', 'max:255'];
            $rules['mail_from_name'] = ['required', 'string', 'max:255'];
        }

        $validated = $request->validate($rules);

        Setting::set('mail_smtp_host', trim((string) ($validated['smtp_host'] ?? '')));
        $port = $validated['smtp_port'] ?? 587;
        Setting::set('mail_smtp_port', (string) $port);
        Setting::set('mail_smtp_username', (string) ($validated['smtp_username'] ?? ''));
        Setting::set('mail_smtp_encryption', (string) ($validated['smtp_encryption'] ?? 'tls'));
        Setting::set('mail_from_address', trim((string) ($validated['mail_from_address'] ?? '')));
        Setting::set('mail_from_name', (string) ($validated['mail_from_name'] ?? ''));
        Setting::set('admin_notification_email', trim((string) ($validated['admin_notification_email'] ?? '')));

        if ($request->filled('smtp_password')) {
            Setting::setEncrypted('mail_smtp_password', $request->input('smtp_password'));
        }

        DatabaseMailConfig::applyIfConfigured();

        return redirect()
            ->route('admin.settings.edit')
            ->with('success', 'Settings saved.');
    }

    public function updateCalendar(Request $request): RedirectResponse
    {
        $enabled = $request->boolean('calendar_event_enabled');

        if (! $enabled) {
            Setting::set('calendar_event_enabled', '0');

            return redirect()
                ->to(route('admin.settings.edit').'#calendar-event')
                ->with('success', 'Add-to-calendar links are turned off for approval emails.');
        }

        $validated = $request->validate([
            'calendar_event_title' => ['required', 'string', 'max:255'],
            'calendar_event_start' => ['required', 'date'],
            'calendar_event_end' => ['required', 'date', 'after:calendar_event_start'],
            'calendar_event_location' => ['nullable', 'string', 'max:500'],
            'calendar_event_description' => ['nullable', 'string', 'max:5000'],
        ]);

        Setting::set('calendar_event_enabled', '1');
        Setting::set('calendar_event_title', $validated['calendar_event_title']);
        Setting::set('calendar_event_start', $validated['calendar_event_start']);
        Setting::set('calendar_event_end', $validated['calendar_event_end']);
        Setting::set('calendar_event_location', $validated['calendar_event_location'] ?? '');
        Setting::set('calendar_event_description', $validated['calendar_event_description'] ?? '');

        return redirect()
            ->to(route('admin.settings.edit').'#calendar-event')
            ->with('success', 'Calendar event saved. “Add to calendar” appears in the Guest — RSVP approved email when links are included in that template.');
    }

    public function updateSms(Request $request): RedirectResponse
    {
        $request->validate([
            RsvpSmsNotifier::SETTING_BODY_SUBMITTED_GUEST => ['nullable', 'string', 'max:2000'],
            RsvpSmsNotifier::SETTING_BODY_SUBMITTED_ADMIN => ['nullable', 'string', 'max:2000'],
            RsvpSmsNotifier::SETTING_BODY_DECISION_GUEST_APPROVED => ['nullable', 'string', 'max:2000'],
            RsvpSmsNotifier::SETTING_BODY_DECISION_GUEST_REJECTED => ['nullable', 'string', 'max:2000'],
            RsvpSmsNotifier::SETTING_BODY_DECISION_ADMIN => ['nullable', 'string', 'max:2000'],
        ]);

        foreach ([
            RsvpSmsNotifier::SETTING_BODY_SUBMITTED_GUEST,
            RsvpSmsNotifier::SETTING_BODY_SUBMITTED_ADMIN,
            RsvpSmsNotifier::SETTING_BODY_DECISION_GUEST_APPROVED,
            RsvpSmsNotifier::SETTING_BODY_DECISION_GUEST_REJECTED,
            RsvpSmsNotifier::SETTING_BODY_DECISION_ADMIN,
        ] as $bodyKey) {
            Setting::set($bodyKey, (string) $request->input($bodyKey, ''));
        }

        Setting::set('sms_guest_on_submit', ($request->input('sms_guest_on_submit') === '1') ? '1' : '0');
        Setting::set('sms_guest_on_approve', ($request->input('sms_guest_on_approve') === '1') ? '1' : '0');
        Setting::set('sms_guest_on_reject', ($request->input('sms_guest_on_reject') === '1') ? '1' : '0');
        Setting::set('sms_admin_on_submit', ($request->input('sms_admin_on_submit') === '1') ? '1' : '0');
        Setting::set('sms_admin_on_decision', ($request->input('sms_admin_on_decision') === '1') ? '1' : '0');

        $enabled = $request->boolean('sms_arkesel_enabled');

        if (! $enabled) {
            Setting::set('sms_arkesel_enabled', '0');

            return redirect()
                ->to(route('admin.settings.edit').'#sms-arkesel')
                ->with('success', 'Arkesel SMS is turned off. Message text was saved.');
        }

        $validated = $request->validate([
            'sms_arkesel_sender' => ['required', 'string', 'max:11'],
            'sms_arkesel_api_key' => ['nullable', 'string', 'max:500'],
            'sms_country_code' => ['required', 'string', 'regex:/^[0-9]{1,4}$/'],
            'admin_notification_phone' => ['nullable', 'string', 'max:30'],
        ]);

        if (! $request->filled('sms_arkesel_api_key') && Setting::getDecrypted('sms_arkesel_api_key') === '') {
            return back()
                ->withErrors(['sms_arkesel_api_key' => 'API key is required when SMS is enabled.'])
                ->withInput();
        }

        Setting::set('sms_arkesel_enabled', '1');
        Setting::set('sms_arkesel_sender', $validated['sms_arkesel_sender']);
        Setting::set('sms_country_code', $validated['sms_country_code']);
        Setting::set('admin_notification_phone', trim((string) ($validated['admin_notification_phone'] ?? '')));

        if ($request->filled('sms_arkesel_api_key')) {
            Setting::setEncrypted('sms_arkesel_api_key', $request->input('sms_arkesel_api_key'));
        }

        return redirect()
            ->to(route('admin.settings.edit').'#sms-arkesel')
            ->with('success', 'Arkesel SMS settings saved.');
    }

    public function destroyAllRsvps(Request $request): RedirectResponse
    {
        $request->validate([
            'confirm_clear_rsvps' => ['accepted'],
        ]);

        $deleted = Rsvp::query()->delete();

        $message = $deleted === 0
            ? 'There were no RSVPs to remove.'
            : 'Removed '.$deleted.' '.($deleted === 1 ? 'RSVP' : 'RSVPs').' from the guest list.';

        return redirect()
            ->route('admin.settings.edit')
            ->with('success', $message);
    }
}
