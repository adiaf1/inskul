<?php

namespace Database\Seeders;

use App\Models\Room;
use App\Models\School;
use Illuminate\Database\Seeder;

class RoomSeeder extends Seeder
{
    public function run(): void
    {
        $schools = School::whereIn('npsn', [
            '10000001',
            '10000002',
            '10000003',
            '10000004',
            '10000005',
        ])->get();

        $rooms = [
            ['name' => 'Ruang Kelas 1', 'code' => 'R-01', 'type' => 'classroom', 'capacity' => 36, 'location' => 'Lantai 1 Gedung A'],
            ['name' => 'Ruang Kelas 2', 'code' => 'R-02', 'type' => 'classroom', 'capacity' => 36, 'location' => 'Lantai 1 Gedung A'],
            ['name' => 'Ruang Kelas 3', 'code' => 'R-03', 'type' => 'classroom', 'capacity' => 36, 'location' => 'Lantai 2 Gedung A'],
            ['name' => 'Lab Komputer', 'code' => 'LAB-KOM', 'type' => 'laboratory', 'capacity' => 30, 'location' => 'Lantai 1 Gedung B'],
            ['name' => 'Lab IPA', 'code' => 'LAB-IPA', 'type' => 'laboratory', 'capacity' => 32, 'location' => 'Lantai 2 Gedung B'],
            ['name' => 'Perpustakaan', 'code' => 'PERPUS', 'type' => 'library', 'capacity' => 40, 'location' => 'Gedung C'],
            ['name' => 'Aula', 'code' => 'AULA', 'type' => 'hall', 'capacity' => 150, 'location' => 'Gedung Utama'],
        ];

        foreach ($schools as $school) {
            foreach ($rooms as $room) {
                Room::updateOrCreate([
                    'school_id' => $school->id,
                    'code' => $room['code'],
                ], [
                    'name' => $room['name'],
                    'type' => $room['type'],
                    'capacity' => $room['capacity'],
                    'location' => $room['location'],
                    'is_active' => true,
                ]);
            }
        }
    }
}
