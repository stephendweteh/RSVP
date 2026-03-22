<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminUserCrudTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        $user = User::factory()->create();
        $user->forceFill(['is_admin' => true])->save();

        return $user->fresh();
    }

    public function test_admin_can_list_users(): void
    {
        $admin = $this->admin();
        User::factory()->count(2)->create();

        $this->actingAs($admin)->get(route('admin.users.index'))->assertOk();
    }

    public function test_admin_can_create_user(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->post(route('admin.users.store'), [
            'name' => 'New Person',
            'email' => 'new@example.com',
            'password' => 'SecurePass1!',
            'password_confirmation' => 'SecurePass1!',
            'is_admin' => '1',
        ])->assertRedirect(route('admin.users.index'));

        $this->assertDatabaseHas('users', [
            'name' => 'New Person',
            'email' => 'new@example.com',
        ]);
        $this->assertTrue(User::query()->where('email', 'new@example.com')->first()->is_admin);
    }

    public function test_admin_can_update_user(): void
    {
        $admin = $this->admin();
        $other = User::factory()->create(['name' => 'Other', 'email' => 'other@example.com']);
        $other->forceFill(['is_admin' => true])->save();

        $this->actingAs($admin)->put(route('admin.users.update', $other), [
            'name' => 'Renamed',
            'email' => 'renamed@example.com',
            'is_admin' => '1',
        ])->assertRedirect(route('admin.users.show', $other));

        $this->assertDatabaseHas('users', [
            'id' => $other->id,
            'name' => 'Renamed',
            'email' => 'renamed@example.com',
        ]);
    }

    public function test_cannot_delete_only_administrator(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->from(route('admin.users.index'))->delete(route('admin.users.destroy', $admin))
            ->assertRedirect(route('admin.users.index'))
            ->assertSessionHasErrors('delete');

        $this->assertDatabaseHas('users', ['id' => $admin->id]);
    }

    public function test_can_delete_non_last_admin_when_two_admins_exist(): void
    {
        $admin = $this->admin();
        $other = User::factory()->create(['email' => 'second@example.com']);
        $other->forceFill(['is_admin' => true])->save();

        $this->actingAs($admin)->delete(route('admin.users.destroy', $other))
            ->assertRedirect(route('admin.users.index'));

        $this->assertDatabaseMissing('users', ['id' => $other->id]);
    }

    public function test_admin_can_create_user_with_profile_picture(): void
    {
        Storage::fake('public');

        $admin = $this->admin();
        $file = UploadedFile::fake()->image('face.jpg', 100, 100);

        $this->actingAs($admin)->post(route('admin.users.store'), [
            'name' => 'Photo User',
            'email' => 'photo@example.com',
            'password' => 'SecurePass1!',
            'password_confirmation' => 'SecurePass1!',
            'avatar' => $file,
        ])->assertRedirect(route('admin.users.index'));

        $user = User::query()->where('email', 'photo@example.com')->first();
        $this->assertNotNull($user->avatar_path);
        Storage::disk('public')->assertExists($user->avatar_path);
    }
}
