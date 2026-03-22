<?php

namespace Tests\Feature;

use App\Mail\RsvpSubmittedMail;
use App\Models\Rsvp;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class RsvpSubmissionTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_submit_rsvp_and_stays_pending(): void
    {
        Mail::fake();

        $this->get(route('rsvp.index'));

        $response = $this->post(route('rsvp.store'), [
            'name' => 'Jane Guest',
            'phone' => '555-0100',
            'email' => 'jane@example.com',
            'attendance' => 'attending',
            'message' => 'See you there',
        ]);

        $response->assertRedirect(route('rsvp.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('rsvps', [
            'name' => 'Jane Guest',
            'phone' => '555-0100',
            'email' => 'jane@example.com',
            'guests_count' => 1,
            'attendance' => 'attending',
            'status' => Rsvp::STATUS_PENDING,
        ]);

        Mail::assertSent(RsvpSubmittedMail::class, function (RsvpSubmittedMail $mail): bool {
            return $mail->rsvp->email === 'jane@example.com';
        });
    }

    public function test_rsvp_requires_email(): void
    {
        $this->get(route('rsvp.index'));

        $this->post(route('rsvp.store'), [
            'name' => 'Someone',
            'phone' => '555-0200',
            'attendance' => 'not_attending',
        ])->assertSessionHasErrors('email');
    }

    public function test_rsvp_validation_requires_name_phone_email_attendance(): void
    {
        $this->get(route('rsvp.index'));

        $this->post(route('rsvp.store'), [
            'name' => '',
            'phone' => '',
            'email' => '',
            'attendance' => '',
        ])->assertSessionHasErrors(['name', 'phone', 'email', 'attendance']);
    }

    public function test_rsvp_form_hidden_when_fully_booked(): void
    {
        for ($i = 1; $i <= Rsvp::APPROVED_CAPACITY; $i++) {
            Rsvp::query()->create([
                'name' => "Guest {$i}",
                'phone' => '555-'.str_pad((string) $i, 4, '0', STR_PAD_LEFT),
                'email' => "g{$i}@example.com",
                'guests_count' => 1,
                'attendance' => 'attending',
                'message' => null,
                'status' => Rsvp::STATUS_APPROVED,
                'table_number' => $i,
            ]);
        }

        $this->get(route('rsvp.index'))
            ->assertOk()
            ->assertSee('RSVP fully booked', false)
            ->assertDontSee('Submit RSVP', false);
    }

    public function test_rsvp_submission_rejected_when_fully_booked(): void
    {
        for ($i = 1; $i <= Rsvp::APPROVED_CAPACITY; $i++) {
            Rsvp::query()->create([
                'name' => "Guest {$i}",
                'phone' => '555-'.str_pad((string) $i, 4, '0', STR_PAD_LEFT),
                'email' => "g{$i}@example.com",
                'guests_count' => 1,
                'attendance' => 'attending',
                'message' => null,
                'status' => Rsvp::STATUS_APPROVED,
                'table_number' => $i,
            ]);
        }

        $this->post(route('rsvp.store'), [
            'name' => 'Late Guest',
            'phone' => '555-9999',
            'email' => 'late@example.com',
            'attendance' => 'attending',
        ])->assertSessionHasErrors('rsvp');

        $this->assertDatabaseMissing('rsvps', ['email' => 'late@example.com']);
    }

    public function test_rsvp_message_is_optional(): void
    {
        Mail::fake();

        $this->get(route('rsvp.index'));

        $this->post(route('rsvp.store'), [
            'name' => 'No Message',
            'phone' => '555-0300',
            'email' => 'nomessage@example.com',
            'attendance' => 'attending',
        ])->assertRedirect(route('rsvp.index'));

        $this->assertDatabaseHas('rsvps', [
            'email' => 'nomessage@example.com',
            'message' => null,
        ]);
    }
}
