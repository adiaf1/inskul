<?php

namespace Database\Seeders;

use App\Models\School;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\Role;

class SchoolAdminSeeder extends Seeder
{
    public function run(): void
    {
        $schoolAdminRole = Role::firstOrCreate([
            'name' => 'school_admin',
            'guard_name' => 'web',
        ]);

        $superAdmin = User::where('email', 'admin@mail.com')->first();

        $schools = [
            [
                'name' => 'SD Nusantara 01',
                'npsn' => '10000001',
                'level' => 'SD',
                'address' => 'Jl. Melati No. 1, Bandung',
                'phone' => '0221000001',
                'email' => 'sd.nusantara01@mail.com',
                'admin_name' => 'Admin SD Nusantara 01',
                'admin_email' => 'admin.sd.nusantara01@mail.com',
            ],
            [
                'name' => 'SMP Harapan Bangsa',
                'npsn' => '10000002',
                'level' => 'SMP',
                'address' => 'Jl. Pendidikan No. 2, Jakarta',
                'phone' => '0211000002',
                'email' => 'smp.harapanbangsa@mail.com',
                'admin_name' => 'Admin SMP Harapan Bangsa',
                'admin_email' => 'admin.smp.harapanbangsa@mail.com',
            ],
            [
                'name' => 'SMA Cendekia Mandiri',
                'npsn' => '10000003',
                'level' => 'SMA',
                'address' => 'Jl. Cendekia No. 3, Yogyakarta',
                'phone' => '02741000003',
                'email' => 'sma.cendekiamandiri@mail.com',
                'admin_name' => 'Admin SMA Cendekia Mandiri',
                'admin_email' => 'admin.sma.cendekiamandiri@mail.com',
            ],
            [
                'name' => 'SMK Teknologi Prima',
                'npsn' => '10000004',
                'level' => 'SMK',
                'address' => 'Jl. Industri No. 4, Surabaya',
                'phone' => '0311000004',
                'email' => 'smk.teknologiprima@mail.com',
                'admin_name' => 'Admin SMK Teknologi Prima',
                'admin_email' => 'admin.smk.teknologiprima@mail.com',
            ],
            [
                'name' => 'MI Al Hikmah',
                'npsn' => '10000005',
                'level' => 'MI',
                'address' => 'Jl. Pesantren No. 5, Semarang',
                'phone' => '0241000005',
                'email' => 'mi.alhikmah@mail.com',
                'admin_name' => 'Admin MI Al Hikmah',
                'admin_email' => 'admin.mi.alhikmah@mail.com',
            ],
        ];

        foreach ($schools as $data) {
            $school = School::updateOrCreate([
                'npsn' => $data['npsn'],
            ], [
                'name' => $data['name'],
                'level' => $data['level'],
                'address' => $data['address'],
                'phone' => $data['phone'],
                'email' => $data['email'],
                'status' => 'pending',
                'approved_at' => null,
                'approved_by' => null,
            ]);

            $admin = User::updateOrCreate([
                'email' => $data['admin_email'],
            ], [
                'name' => $data['admin_name'],
                'password' => Hash::make('password'),
                'status' => 'pending',
            ]);

            $admin->syncRoles(['school_admin']);

            $school->users()->syncWithoutDetaching([
                $admin->id => [
                    'role_id' => $schoolAdminRole->id,
                    'status' => 'pending',
                ],
            ]);
        }
    }
}
