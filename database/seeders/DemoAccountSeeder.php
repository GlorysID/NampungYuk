<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoAccountSeeder extends Seeder
{
    /**
     * Login-ready dummy account for manual QA (email already verified).
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'demo@nampungyuk.test'],
            [
                'name' => 'Demo User',
                'username' => 'demo',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'bio' => 'Akun dummy untuk testing login.',
            ]
        );
    }
}
