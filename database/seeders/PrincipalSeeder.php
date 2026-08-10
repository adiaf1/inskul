<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\School;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class PrincipalSeeder extends Seeder
{
    public function run(): void
    {
        $principalRole = Role::firstOrCreate([
            'name' => 'principal',
            'guard_name' => 'web',
        ]);

        School::query()
            ->where('status', 'active')
            ->orderBy('name')
            ->get()
            ->each(function (School $school) use ($principalRole) {
                $baseEmail = $school->email
                    ? 'principal.'.$school->email
                    : 'principal.'.strtolower(str_replace(' ', '.', $school->name)).'@mail.com';

                $principal = User::updateOrCreate([
                    'email' => $baseEmail,
                ], [
                    'name' => 'Kepala '.$school->name,
                    'password' => Hash::make('password'),
                    'status' => 'active',
                ]);

                $principal->syncRoles(['principal']);

                $school->users()->syncWithoutDetaching([
                    $principal->id => [
                        'role_id' => $principalRole->id,
                        'status' => 'active',
                    ],
                ]);
            });
    }
}
