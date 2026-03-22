<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminManagerAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_manager_gets_403_on_settings(): void
    {
        $manager = User::factory()->manager()->create();

        $this->actingAs($manager)->get(route('admin.settings.edit'))->assertForbidden();
    }

    public function test_manager_can_open_dashboard(): void
    {
        $manager = User::factory()->manager()->create();

        $this->actingAs($manager)->get(route('admin.dashboard'))->assertOk();
    }

    public function test_manager_can_open_rsvps(): void
    {
        $manager = User::factory()->manager()->create();

        $this->actingAs($manager)->get(route('admin.rsvps.index'))->assertOk();
    }

    public function test_manager_forbidden_on_users_index(): void
    {
        $manager = User::factory()->manager()->create();

        $this->actingAs($manager)->get(route('admin.users.index'))->assertForbidden();
    }

    public function test_manager_can_view_own_profile(): void
    {
        $manager = User::factory()->manager()->create();

        $this->actingAs($manager)->get(route('admin.users.show', $manager))->assertOk();
    }

    public function test_manager_forbidden_on_other_user_profile(): void
    {
        $manager = User::factory()->manager()->create();
        $other = User::factory()->create();

        $this->actingAs($manager)->get(route('admin.users.show', $other))->assertForbidden();
    }
}
