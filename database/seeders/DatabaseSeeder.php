<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create a test user with a hashed password
        User::create([
            'email' => 'amkexchangerate@gmail.com',
            'password' => Hash::make('amk@123456'),
        ]);
    }
}
