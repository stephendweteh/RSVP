<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Models\User;
use App\Services\ArkeselSmsService;
use App\Services\RsvpSmsNotifier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ArkeselSmsTest extends TestCase
{
    use RefreshDatabase;

    private function makeAdmin(): User
    {
        $user = User::factory()->create(['email' => 'admin@example.com']);
        $user->forceFill(['is_admin' => true])->save();

        return $user->fresh();
    }

    public function test_admin_can_save_sms_settings(): void
    {
        $admin = $this->makeAdmin();

        $this->actingAs($admin)
            ->put(route('admin.settings.sms.update'), [
                'sms_arkesel_enabled' => '1',
                'sms_arkesel_sender' => 'MyWedding',
                'sms_arkesel_api_key' => 'secret-api-key',
                'sms_country_code' => '233',
                'admin_notification_phone' => '0550111222',
                'sms_guest_on_submit' => '1',
                'sms_guest_on_approve' => '0',
                'sms_guest_on_reject' => '1',
                'sms_admin_on_submit' => '1',
                'sms_admin_on_decision' => '0',
            ])
            ->assertRedirect(route('admin.settings.edit').'#sms-arkesel')
            ->assertSessionHas('success');

        $this->assertSame('1', Setting::get('sms_arkesel_enabled'));
        $this->assertSame('MyWedding', Setting::get('sms_arkesel_sender'));
        $this->assertSame('233', Setting::get('sms_country_code'));
        $this->assertSame('secret-api-key', Setting::getDecrypted('sms_arkesel_api_key'));
        $this->assertSame('0550111222', Setting::get('admin_notification_phone'));
        $this->assertSame('1', Setting::get('sms_guest_on_submit'));
        $this->assertSame('0', Setting::get('sms_guest_on_approve'));
    }

    public function test_admin_can_disable_sms_without_clearing_key(): void
    {
        $admin = $this->makeAdmin();
        Setting::set('sms_arkesel_enabled', '1');
        Setting::setEncrypted('sms_arkesel_api_key', 'keep-me');

        $this->actingAs($admin)
            ->put(route('admin.settings.sms.update'), [
                'sms_arkesel_enabled' => '0',
            ])
            ->assertRedirect(route('admin.settings.edit').'#sms-arkesel');

        $this->assertSame('0', Setting::get('sms_arkesel_enabled'));
        $this->assertSame('keep-me', Setting::getDecrypted('sms_arkesel_api_key'));
    }

    public function test_rsvp_submit_sends_guest_sms_when_configured(): void
    {
        Mail::fake();
        Http::fake([
            ArkeselSmsService::API_URL => Http::response(['status' => 'success'], 200),
        ]);

        Setting::set('sms_arkesel_enabled', '1');
        Setting::set('sms_arkesel_sender', 'RSVPApp');
        Setting::set('sms_country_code', '233');
        Setting::setEncrypted('sms_arkesel_api_key', 'test-key');
        Setting::set('sms_guest_on_submit', '1');

        $this->get(route('rsvp.index'));

        $this->post(route('rsvp.store'), [
            'name' => 'SMS Guest',
            'phone' => '0554019953',
            'email' => 'sms@example.com',
            'attendance' => 'attending',
        ])->assertRedirect(route('rsvp.index'));

        Http::assertSent(function ($request): bool {
            if ($request->url() !== ArkeselSmsService::API_URL) {
                return false;
            }

            $data = $request->data();
            $message = (string) ($data['message'] ?? '');

            return $request->hasHeader('api-key', 'test-key')
                && ($data['sender'] ?? null) === 'RSVPApp'
                && ($data['recipients'] ?? null) === ['233554019953']
                && str_contains($message, 'SMS Guest')
                && str_contains($message, 'Thank you for your RSVP');
        });
    }

    public function test_rsvp_submit_sms_uses_custom_template_when_set(): void
    {
        Mail::fake();
        Http::fake([
            ArkeselSmsService::API_URL => Http::response(['status' => 'success'], 200),
        ]);

        Setting::set('sms_arkesel_enabled', '1');
        Setting::set('sms_arkesel_sender', 'RSVPApp');
        Setting::set('sms_country_code', '233');
        Setting::setEncrypted('sms_arkesel_api_key', 'test-key');
        Setting::set('sms_guest_on_submit', '1');
        Setting::set(RsvpSmsNotifier::SETTING_BODY_SUBMITTED_GUEST, 'Custom hello {{guest_name}} end.');

        $this->get(route('rsvp.index'));

        $this->post(route('rsvp.store'), [
            'name' => 'Pat',
            'phone' => '0554019953',
            'email' => 'pat@example.com',
            'attendance' => 'attending',
        ])->assertRedirect(route('rsvp.index'));

        Http::assertSent(function ($request): bool {
            $data = $request->data();

            return ($data['message'] ?? null) === 'Custom hello Pat end.';
        });
    }
}
