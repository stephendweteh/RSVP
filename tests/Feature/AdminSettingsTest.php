<?php

namespace Tests\Feature;

use App\Models\Rsvp;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminSettingsTest extends TestCase
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

    public function test_admin_can_open_settings(): void
    {
        $admin = $this->makeAdmin();

        $this->actingAs($admin)->get(route('admin.settings.edit'))
            ->assertOk()
            ->assertSee('Email content', false)
            ->assertSee('SMS (Arkesel)', false)
            ->assertSee('Message text', false)
            ->assertSee('Guest list (RSVPs)', false)
            ->assertSee('Clear all RSVPs', false);
    }

    public function test_admin_can_clear_all_rsvps_with_confirmation(): void
    {
        $admin = $this->makeAdmin();
        Rsvp::query()->create([
            'name' => 'A',
            'phone' => '1',
            'email' => 'a@example.com',
            'guests_count' => 1,
            'attendance' => 'attending',
            'message' => null,
            'status' => Rsvp::STATUS_PENDING,
        ]);
        Rsvp::query()->create([
            'name' => 'B',
            'phone' => '2',
            'email' => 'b@example.com',
            'guests_count' => 1,
            'attendance' => 'not_attending',
            'message' => null,
            'status' => Rsvp::STATUS_APPROVED,
        ]);

        $this->actingAs($admin)
            ->delete(route('admin.settings.rsvps.destroy-all'), [
                'confirm_clear_rsvps' => '1',
            ])
            ->assertRedirect(route('admin.settings.edit'))
            ->assertSessionHas('success');

        $this->assertSame(0, Rsvp::query()->count());
    }

    public function test_clear_all_rsvps_requires_confirmation(): void
    {
        $admin = $this->makeAdmin();
        Rsvp::query()->create([
            'name' => 'A',
            'phone' => '1',
            'email' => 'a@example.com',
            'guests_count' => 1,
            'attendance' => 'attending',
            'message' => null,
            'status' => Rsvp::STATUS_PENDING,
        ]);

        $this->actingAs($admin)
            ->delete(route('admin.settings.rsvps.destroy-all'), [])
            ->assertSessionHasErrors('confirm_clear_rsvps');

        $this->assertSame(1, Rsvp::query()->count());
    }
}
