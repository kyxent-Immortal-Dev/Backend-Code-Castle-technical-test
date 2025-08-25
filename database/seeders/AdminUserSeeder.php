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
        if (!User::where('email', 'h.ezequiel.z.campos@codecastle.com')->exists()) {
            User::create([
                'name' => 'Humberto Ezequiel Zelaya Campos',
                'email' => 'h.ezequiel.z.campos@codecastle.com',
                'password' => Hash::make('admin123'),
                'role' => 'admin',
                'is_active' => true,
            ]);
        }

        // Create a sample vendedor user if it doesn't exist
        if (!User::where('email', 'vendedor@codecastle.com')->exists()) {
            User::create([
                'name' => 'John Doe',
                'email' => 'vendedor@codecastle.com',
                'password' => Hash::make('vendedor123'),
                'role' => 'vendedor',
                'is_active' => true,
            ]);
        }
    }
}
