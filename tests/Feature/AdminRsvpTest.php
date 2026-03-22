<?php

namespace Tests\Feature;

use App\Mail\RsvpDecisionMail;
use App\Models\Rsvp;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class AdminRsvpTest extends TestCase
{
    use RefreshDatabase;

    private function makeAdmin(): User
    {
        return User::factory()->administrator()->create([
            'email' => 'admin@example.com',
        ]);
    }

    private function makeRsvp(array $overrides = []): Rsvp
    {
        return Rsvp::query()->create(array_merge([
            'name' => 'Guest One',
            'phone' => '555-1111',
            'email' => 'guest@example.com',
            'guests_count' => 2,
            'attendance' => 'attending',
            'message' => null,
            'status' => Rsvp::STATUS_PENDING,
        ], $overrides));
    }

    public function test_admin_can_log_in_and_reach_dashboard(): void
    {
        $this->makeAdmin();

        $this->get(route('admin.login'))->assertOk();

        $response = $this->post('/admin/login', [
            'email' => 'admin@example.com',
            'password' => 'password',
        ]);

        $response->assertRedirect(route('admin.dashboard'));
        $this->get(route('admin.dashboard'))->assertOk();
    }

    public function test_non_admin_cannot_log_in_to_admin_area(): void
    {
        User::factory()->create([
            'email' => 'user@example.com',
        ]);

        $this->post('/admin/login', [
            'email' => 'user@example.com',
            'password' => 'password',
        ])->assertSessionHasErrors('email');
    }

    public function test_admin_can_approve_rsvp_and_email_sent_when_guest_has_email(): void
    {
        Mail::fake();

        $admin = $this->makeAdmin();
        $rsvp = $this->makeRsvp();

        $this->actingAs($admin)->post(route('admin.rsvps.approve', $rsvp->id))->assertRedirect();

        $this->assertDatabaseHas('rsvps', [
            'id' => $rsvp->id,
            'status' => Rsvp::STATUS_APPROVED,
            'table_number' => 1,
        ]);

        Mail::assertSent(RsvpDecisionMail::class, function (RsvpDecisionMail $mail) use ($rsvp): bool {
            return $mail->rsvp->is($rsvp) && $mail->decision === Rsvp::STATUS_APPROVED;
        });
    }

    public function test_admin_cannot_approve_when_capacity_reached(): void
    {
        $admin = $this->makeAdmin();

        for ($i = 1; $i <= Rsvp::APPROVED_CAPACITY; $i++) {
            $this->makeRsvp([
                'name' => "Guest {$i}",
                'email' => "g{$i}@example.com",
                'status' => Rsvp::STATUS_APPROVED,
                'table_number' => $i,
            ]);
        }

        $pending = $this->makeRsvp([
            'name' => 'Waitlist',
            'email' => 'wait@example.com',
            'status' => Rsvp::STATUS_PENDING,
        ]);

        $this->actingAs($admin)
            ->post(route('admin.rsvps.approve', $pending->id))
            ->assertSessionHasErrors('capacity');

        $this->assertDatabaseHas('rsvps', [
            'id' => $pending->id,
            'status' => Rsvp::STATUS_PENDING,
        ]);
    }

    public function test_admin_can_export_csv(): void
    {
        $admin = $this->makeAdmin();
        $this->makeRsvp(['name' => 'CSV Guest']);

        $response = $this->actingAs($admin)->get(route('admin.rsvps.export'));

        $response->assertOk();
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');
        $this->assertStringContainsString('CSV Guest', $response->streamedContent());
    }
}
