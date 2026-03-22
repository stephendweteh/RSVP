<?php

namespace Tests\Feature;

use App\Mail\MailTemplateSeed;
use App\Models\MailTemplate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminMailTemplateTest extends TestCase
{
    use RefreshDatabase;

    private function makeAdmin(): User
    {
        $user = User::factory()->create([
            'email' => 'admin@example.com',
        ]);
        $user->forceFill(['is_admin' => true])->save();

        return $user->fresh();
    }

    public function test_settings_lists_email_templates(): void
    {
        $admin = $this->makeAdmin();

        $this->actingAs($admin)->get(route('admin.settings.edit'))
            ->assertOk()
            ->assertSee('Email content', false)
            ->assertSee('Guest — RSVP submitted', false);
    }

    public function test_admin_can_update_mail_template(): void
    {
        $admin = $this->makeAdmin();
        $tpl = MailTemplate::query()->where('slug', MailTemplate::SLUG_RSVP_SUBMITTED_GUEST)->firstOrFail();

        $this->actingAs($admin)
            ->put(route('admin.mail-templates.update', $tpl), [
                'name' => $tpl->name,
                'description' => $tpl->description,
                'subject' => 'Custom subject line',
                'body_html' => '<p>Hi {{guest_name}}</p>',
                'body_text' => 'Hi {{guest_name}}',
            ])
            ->assertRedirect(route('admin.settings.edit').'#email-templates');

        $tpl->refresh();
        $this->assertSame('Custom subject line', $tpl->subject);
        $this->assertStringContainsString('Hi {{guest_name}}', $tpl->body_html);
    }

    public function test_admin_can_reset_mail_template(): void
    {
        $admin = $this->makeAdmin();
        $tpl = MailTemplate::query()->where('slug', MailTemplate::SLUG_RSVP_SUBMITTED_GUEST)->firstOrFail();
        $tpl->update(['subject' => 'Broken subject']);

        $this->actingAs($admin)
            ->post(route('admin.mail-templates.reset', $tpl))
            ->assertRedirect(route('admin.mail-templates.edit', $tpl));

        $tpl->refresh();
        $defaults = MailTemplateSeed::forSlug($tpl->slug);
        $this->assertNotNull($defaults);
        $this->assertSame($defaults['subject'], $tpl->subject);
    }
}
