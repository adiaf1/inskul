<?php

namespace Database\Seeders;

use App\Models\School;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class TeacherSeeder extends Seeder
{
    public function run(): void
    {
        $teacherRole = Role::firstOrCreate([
            'name' => 'teacher',
            'guard_name' => 'web',
        ]);

        $schools = School::whereIn('npsn', [
            '10000001',
            '10000002',
            '10000003',
            '10000004',
            '10000005',
        ])->get()->keyBy('npsn');

        $teacherNames = [
            ['name' => 'Budi Santoso', 'gender' => 'male', 'birth_place' => 'Bandung'],
            ['name' => 'Siti Aminah', 'gender' => 'female', 'birth_place' => 'Jakarta'],
            ['name' => 'Agus Pratama', 'gender' => 'male', 'birth_place' => 'Yogyakarta'],
            ['name' => 'Dewi Lestari', 'gender' => 'female', 'birth_place' => 'Semarang'],
            ['name' => 'Rina Kurniawati', 'gender' => 'female', 'birth_place' => 'Surabaya'],
        ];

        foreach ($schools as $school) {
            foreach ($teacherNames as $index => $teacherData) {
                $number = $index + 1;
                $schoolCode = substr((string) $school->npsn, -2);
                $email = 'guru'.$schoolCode.'.'.$number.'@mail.com';

                $user = User::updateOrCreate([
                    'email' => $email,
                ], [
                    'name' => $teacherData['name'].' '.$school->level,
                    'password' => Hash::make('password'),
                    'status' => 'active',
                ]);

                $user->syncRoles(['teacher']);

                $school->users()->syncWithoutDetaching([
                    $user->id => [
                        'role_id' => $teacherRole->id,
                        'status' => 'active',
                    ],
                ]);

                Teacher::updateOrCreate([
                    'school_id' => $school->id,
                    'user_id' => $user->id,
                ], [
                    'nip' => '198'.str_pad((string) $schoolCode, 2, '0', STR_PAD_LEFT).str_pad((string) $number, 2, '0', STR_PAD_LEFT).'202606100'.$number,
                    'nuptk' => '88'.$school->npsn.str_pad((string) $number, 2, '0', STR_PAD_LEFT),
                    'employee_number' => 'G'.$schoolCode.str_pad((string) $number, 3, '0', STR_PAD_LEFT),
                    'gender' => $teacherData['gender'],
                    'phone' => '08123'.$schoolCode.str_pad((string) $number, 4, '0', STR_PAD_LEFT),
                    'birth_place' => $teacherData['birth_place'],
                    'birth_date' => '1988-0'.$number.'-12',
                    'address' => 'Alamat guru '.$number.' - '.$school->name,
                    'is_active' => true,
                ]);
            }
        }
    }
}
