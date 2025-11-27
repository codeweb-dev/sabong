<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $roles = ['admin', 'declarator', 'user'];
        foreach ($roles as $roleName) {
            Role::firstOrCreate(['name' => $roleName]);
        }

        $admin = User::firstOrCreate(
            [
                'username' => 'admin',
                'cash'     => 0,
                'password' => '1234',
            ]
        );

        $declarator = User::firstOrCreate(
            [
                'username' => 'declarator',
                'cash'     => 0,
                'password' => '1234',
            ]
        );

        $user = User::firstOrCreate(
            [
                'username' => 'user',
                'cash'     => 0,
                'password' => '1234',
            ]
        );

        $admin->assignRole('admin');
        $declarator->assignRole('declarator');
        $user->assignRole('user');
    }
}
