<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Step 1: Create roles if they don't exist
        $roles = ['admin', 'declarator', 'user'];
        foreach ($roles as $roleName) {
            Role::firstOrCreate(['name' => $roleName]);
        }

        // Step 2: Create the admin user
        $admin = User::firstOrCreate(
            [
                'username' => 'Admin',
                'password' => Hash::make('labrague123'),
            ]
        );

        $declarator = User::firstOrCreate(
            [
                'username' => 'Declarator',
                'password' => Hash::make('labrague123'),
            ]
        );

        $user = User::firstOrCreate(
            [
                'username' => 'User',
                'password' => Hash::make('labrague123'),
            ]
        );

        // Step 3: Assign 'admin' role
        $admin->assignRole('admin');
        $declarator->assignRole('declarator');
        $user->assignRole('user');

        // User::factory(10)->create();

        // User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);
    }
}
