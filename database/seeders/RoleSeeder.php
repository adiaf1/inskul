<?php

namespace Database\Seeders;

use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use App\Models\User;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (['admin', 'editor', 'guest'] as $role) {
            Role::firstOrCreate(['name' => $role]);
        }

        // Buat user admin
        $admin = User::updateOrCreate([
            'email' => 'admin@mail.com',
        ], [
            'name' => 'Admin',
            'password' => Hash::make('password'),
        ]);
        $admin->assignRole('admin');

        // Buat user editor
        $editor = User::updateOrCreate([
            'email' => 'editor@mail.com',
        ], [
            'name' => 'Editor',
            'password' => Hash::make('password'),
        ]);
        $editor->assignRole('editor');

        // Buat user guest
        $guest = User::updateOrCreate([
            'email' => 'guest@mail.com',
        ], [
            'name' => 'Guest',
            'password' => Hash::make('password'),
        ]);
        $guest->assignRole('guest');
    }

}
