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

        $roles = [
            'super_admin',
            'school_admin',
            'principal',
            'teacher',
            'student',
            'parent',
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate([
                'name' => $role,
                'guard_name' => 'web',
            ]);
        }

        // Akun pengelola aplikasi utama.
        $superAdmin = User::updateOrCreate([
            'email' => 'admin@mail.com',
        ], [
            'name' => 'Super Admin',
            'password' => Hash::make('password'),
        ]);
        $superAdmin->syncRoles(['super_admin']);

        // Akun contoh untuk kebutuhan pengembangan awal.
        $schoolAdmin = User::updateOrCreate([
            'email' => 'school.admin@mail.com',
        ], [
            'name' => 'Admin Sekolah',
            'password' => Hash::make('password'),
        ]);
        $schoolAdmin->syncRoles(['school_admin']);

        $teacher = User::updateOrCreate([
            'email' => 'teacher@mail.com',
        ], [
            'name' => 'Guru',
            'password' => Hash::make('password'),
        ]);
        $teacher->syncRoles(['teacher']);

        $student = User::updateOrCreate([
            'email' => 'student@mail.com',
        ], [
            'name' => 'Siswa',
            'password' => Hash::make('password'),
        ]);
        $student->syncRoles(['student']);

        $parent = User::updateOrCreate([
            'email' => 'parent@mail.com',
        ], [
            'name' => 'Orang Tua',
            'password' => Hash::make('password'),
        ]);
        $parent->syncRoles(['parent']);
    }

}
