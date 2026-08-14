<?php

namespace Database\Seeders;

use App\Models\Module;
use App\Models\School;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Auth;

class ModuleSeeder extends Seeder
{
    private const MODULES = [
        [
            'code' => 'exams',
            'name' => 'Ujian',
            'description' => 'Modul ujian, bank soal, pengerjaan ujian, dan hasil ujian.',
            'sort_order' => 10,
        ],
        [
            'code' => 'daily_attendance',
            'name' => 'Presensi Harian',
            'description' => 'Scan datang dan pulang murid tanpa memilih kelas.',
            'sort_order' => 20,
        ],
        [
            'code' => 'class_attendance',
            'name' => 'Presensi Per Kelas',
            'description' => 'Presensi harian berdasarkan rombel atau kelas.',
            'sort_order' => 30,
        ],
        [
            'code' => 'schedule_attendance',
            'name' => 'Presensi Per Jadwal',
            'description' => 'Presensi per sesi pelajaran berdasarkan jadwal.',
            'sort_order' => 40,
        ],
        [
            'code' => 'teacher_attendance',
            'name' => 'Presensi Guru',
            'description' => 'Presensi datang dan pulang guru menggunakan selfie dan geolocation.',
            'sort_order' => 50,
        ],
    ];

    public function run(): void
    {
        foreach (self::MODULES as $moduleData) {
            Module::updateOrCreate([
                'code' => $moduleData['code'],
            ], $moduleData + [
                'is_active' => true,
            ]);
        }

        $modules = Module::query()->where('is_active', true)->get();
        $enabledBy = Auth::id();

        School::query()->where('status', 'active')->get()->each(function (School $school) use ($modules, $enabledBy) {
            foreach ($modules as $module) {
                $school->modules()->syncWithoutDetaching([
                    $module->id => [
                        'is_enabled' => true,
                        'enabled_at' => now(),
                        'enabled_by' => $enabledBy,
                    ],
                ]);
            }
        });
    }
}
