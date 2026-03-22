<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $email = env('ADMIN_EMAIL', 'admin@rsvp.local');
        $password = env('ADMIN_PASSWORD', 'changeme');

        $user = User::query()->updateOrCreate(
            ['email' => $email],
            [
                'name' => env('ADMIN_NAME', 'Admin'),
                'password' => Hash::make($password),
            ],
        );
        $user->forceFill([
            'is_admin' => true,
            'admin_role' => User::ADMIN_ROLE_ADMIN,
        ])->save();
    }
}
