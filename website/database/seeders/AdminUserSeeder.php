<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@bunihayu.com'],
            [
                'name' => 'Admin Bunihayu',
                'password' => Hash::make('password'), // Change this in production
                'role' => 'admin',
            ]
        );
    }
}
