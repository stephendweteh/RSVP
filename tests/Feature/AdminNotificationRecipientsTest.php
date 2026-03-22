<?php

namespace Tests\Feature;

use App\Mail\RsvpSubmittedAdminMail;
use App\Models\Setting;
use App\Models\User;
use App\Services\ArkeselSmsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class AdminNotificationRecipientsTest extends TestCase
{
    use RefreshDatabase;

    public function test_rsvp_sends_admin_mail_to_each_admin_email(): void
    {
        Mail::fake();

        foreach (['a@example.com', 'b@example.com'] as $email) {
            User::factory()->administrator()->create(['email' => $email]);
        }

        $this->get(route('rsvp.index'));

        $this->post(route('rsvp.store'), [
            'name' => 'Guest',
            'phone' => '0554019953',
            'email' => 'guest@example.com',
            'attendance' => 'attending',
        ])->assertRedirect(route('rsvp.index'));

        Mail::assertSent(RsvpSubmittedAdminMail::class, 2);
    }

    public function test_rsvp_includes_extra_notification_email_without_duplicating_admin(): void
    {
        Mail::fake();

        User::factory()->administrator()->create(['email' => 'admin@example.com']);

        Setting::set('admin_notification_email', 'admin@example.com');

        $this->get(route('rsvp.index'));

        $this->post(route('rsvp.store'), [
            'name' => 'Guest',
            'phone' => '0554019953',
            'email' => 'guest@example.com',
            'attendance' => 'attending',
        ])->assertRedirect(route('rsvp.index'));

        Mail::assertSent(RsvpSubmittedAdminMail::class, 1);
    }

    public function test_admin_sms_sent_to_each_admin_phone_deduped(): void
    {
        Mail::fake();
        Http::fake([
            ArkeselSmsService::API_URL => Http::response(['status' => 'success'], 200),
        ]);

        Setting::set('sms_arkesel_enabled', '1');
        Setting::set('sms_arkesel_sender', 'RSVP');
        Setting::set('sms_country_code', '233');
        Setting::setEncrypted('sms_arkesel_api_key', 'k');
        Setting::set('sms_admin_on_submit', '1');

        User::factory()->administrator()->create(['phone' => '0554111111']);
        User::factory()->administrator()->create(['phone' => '0554222222']);

        $this->get(route('rsvp.index'));

        $this->post(route('rsvp.store'), [
            'name' => 'Guest',
            'phone' => '0554333333',
            'email' => 'guest@example.com',
            'attendance' => 'attending',
        ])->assertRedirect(route('rsvp.index'));

        Http::assertSentCount(2);
    }
}
