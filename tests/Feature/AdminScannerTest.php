<?php

namespace Tests\Feature;

use App\Models\Rsvp;
use App\Models\User;
use App\Services\RsvpCheckInQrService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminScannerTest extends TestCase
{
    use RefreshDatabase;

    private function staff(): User
    {
        return User::factory()->manager()->create();
    }

    public function test_scanner_page_requires_auth(): void
    {
        $this->get(route('admin.scanner.index'))->assertRedirect(route('admin.login'));
    }

    public function test_staff_can_open_scanner(): void
    {
        $this->actingAs($this->staff())->get(route('admin.scanner.index'))->assertOk();
    }

    public function test_admit_sets_checked_in_at(): void
    {
        $user = $this->staff();
        $rsvp = Rsvp::query()->create([
            'name' => 'Gate Guest',
            'phone' => '555-0001',
            'email' => 'gate@example.com',
            'guests_count' => 2,
            'attendance' => 'attending',
            'message' => null,
            'status' => Rsvp::STATUS_APPROVED,
            'table_number' => 5,
            'check_in_token' => str_repeat('a', 40),
            'checked_in_at' => null,
        ]);

        $url = RsvpCheckInQrService::qrPayload($rsvp);
        $this->assertNotNull($url);

        $this->actingAs($user)->postJson(route('admin.scanner.admit'), [
            'payload' => $url,
        ])->assertOk()
            ->assertJsonPath('ok', true)
            ->assertJsonPath('already_admitted', false)
            ->assertJsonPath('guest_name', 'Gate Guest');

        $this->assertNotNull($rsvp->fresh()->checked_in_at);
    }

    public function test_second_admit_reports_already_admitted(): void
    {
        $user = $this->staff();
        $rsvp = Rsvp::query()->create([
            'name' => 'Twice',
            'phone' => '555-0002',
            'email' => 'twice@example.com',
            'guests_count' => 1,
            'attendance' => 'attending',
            'message' => null,
            'status' => Rsvp::STATUS_APPROVED,
            'table_number' => 1,
            'check_in_token' => str_repeat('b', 40),
            'checked_in_at' => now()->subHour(),
        ]);

        $this->actingAs($user)->postJson(route('admin.scanner.admit'), [
            'payload' => $rsvp->check_in_token,
        ])->assertOk()
            ->assertJsonPath('ok', true)
            ->assertJsonPath('already_admitted', true);
    }

    public function test_admission_page_shows_valid_guest(): void
    {
        $rsvp = Rsvp::query()->create([
            'name' => 'Public Guest',
            'phone' => '555-0003',
            'email' => 'pub@example.com',
            'guests_count' => 1,
            'attendance' => 'attending',
            'message' => null,
            'status' => Rsvp::STATUS_APPROVED,
            'table_number' => 2,
            'check_in_token' => str_repeat('c', 40),
            'checked_in_at' => null,
        ]);

        $this->get(route('rsvp.admission.show', ['token' => $rsvp->check_in_token]))
            ->assertOk()
            ->assertSee('Public Guest', false)
            ->assertSee('Table 2', false);
    }
}
