<?php

namespace Database\Seeders;

use App\Models\School;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\Role;

class StudentSeeder extends Seeder
{
    public function run(): void
    {
        $studentRole = Role::firstOrCreate([
            'name' => 'student',
            'guard_name' => 'web',
        ]);

        $schools = School::whereIn('npsn', [
            '10000001',
            '10000002',
            '10000003',
            '10000004',
            '10000005',
        ])->get()->keyBy('npsn');

        $firstNames = [
            'Ahmad',
            'Siti',
            'Bima',
            'Dewi',
            'Rizky',
            'Nadia',
            'Fajar',
            'Putri',
            'Ilham',
            'Aulia',
            'Dimas',
            'Rani',
            'Bagas',
            'Intan',
            'Farhan',
            'Maya',
            'Yoga',
            'Laras',
            'Rafi',
            'Nabila',
        ];

        $lastNames = [
            'Pratama',
            'Lestari',
            'Saputra',
            'Anggraini',
            'Maulana',
            'Permata',
            'Ramadhan',
            'Safitri',
            'Firmansyah',
            'Kusuma',
            'Setiawan',
            'Utami',
            'Wibowo',
            'Maharani',
            'Hidayat',
            'Puspita',
            'Nugroho',
            'Cahyani',
            'Akbar',
            'Azzahra',
        ];

        foreach ($schools as $school) {
            $schoolCode = substr((string) $school->npsn, -2);

            for ($number = 1; $number <= 20; $number++) {
                $email = 'murid'.$schoolCode.'.'.$number.'@mail.com';
                $gender = $number % 2 === 0 ? 'female' : 'male';
                $name = $firstNames[$number - 1].' '.$lastNames[$number - 1].' '.$school->level;

                $user = User::updateOrCreate([
                    'email' => $email,
                ], [
                    'name' => $name,
                    'password' => Hash::make('password'),
                    'status' => 'active',
                ]);

                $user->syncRoles(['student']);

                $school->users()->syncWithoutDetaching([
                    $user->id => [
                        'role_id' => $studentRole->id,
                        'status' => 'active',
                    ],
                ]);

                Student::updateOrCreate([
                    'school_id' => $school->id,
                    'user_id' => $user->id,
                ], [
                    'nis' => '26'.$schoolCode.str_pad((string) $number, 4, '0', STR_PAD_LEFT),
                    'nisn' => '00'.$school->npsn.str_pad((string) $number, 2, '0', STR_PAD_LEFT),
                    'entry_year' => $number <= 10 ? 2025 : 2026,
                    'gender' => $gender,
                    'phone' => '08223'.$schoolCode.str_pad((string) $number, 4, '0', STR_PAD_LEFT),
                    'birth_place' => $this->birthPlace($number),
                    'birth_date' => '2010-'.str_pad((string) (($number % 12) + 1), 2, '0', STR_PAD_LEFT).'-'.str_pad((string) (($number % 27) + 1), 2, '0', STR_PAD_LEFT),
                    'address' => 'Alamat murid '.$number.' - '.$school->name,
                    'is_active' => true,
                ]);
            }
        }
    }

    private function birthPlace(int $number): string
    {
        $places = [
            'Bandung',
            'Jakarta',
            'Yogyakarta',
            'Semarang',
            'Surabaya',
        ];

        return $places[($number - 1) % count($places)];
    }
}
