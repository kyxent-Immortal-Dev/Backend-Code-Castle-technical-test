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
        // Create admin user if it doesn't exist
        if (!User::where('email', 'admin@system.com')->exists()) {
            User::create([
                'name' => 'System Administrator',
                'email' => 'admin@system.com',
                'password' => Hash::make('admin123'),
                'role' => 'admin',
                'is_active' => true,
            ]);
        }

        // Create a sample vendedor user if it doesn't exist
        if (!User::where('email', 'vendedor@system.com')->exists()) {
            User::create([
                'name' => 'Sample Vendedor',
                'email' => 'vendedor@system.com',
                'password' => Hash::make('vendedor123'),
                'role' => 'vendedor',
                'is_active' => true,
            ]);
        }
    }
}
