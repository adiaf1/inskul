<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\AttendanceRecord;
use App\Models\AttendanceSession;
use App\Models\Classroom;
use App\Models\Role;
use App\Models\Room;
use App\Models\Schedule;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\Semester;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DemoSchoolDataSeeder extends Seeder
{
    private const ACADEMIC_YEAR = '2025/2026';

    private const PASSWORD = 'password';

    public function run(): void
    {
        DB::transaction(function () {
            $roles = $this->roles();

            foreach ($this->schools() as $schoolData) {
                $school = $this->seedSchool($schoolData, $roles['school_admin'], $roles['principal']);
                $academicYear = $this->seedAcademicYear($school);
                $semester = $this->seedSemesters($school, $academicYear);
                $subjects = $this->seedSubjects($school, $schoolData['subjects']);
                $rooms = $this->seedRooms($school);
                $teachers = $this->seedTeachers($school, $schoolData, $roles['teacher']);
                $schoolClasses = $this->seedClasses($school, $academicYear, $schoolData['levels']);
                $studentsByEntryYear = $this->seedStudents($school, $schoolData, $roles['student']);
                $classrooms = $this->seedClassrooms($school, $academicYear, $semester, $schoolData['levels'], $schoolClasses, $teachers, $studentsByEntryYear);

                $this->seedSchedules($school, $academicYear, $semester, $classrooms, $subjects, $teachers, $rooms);
                $this->seedDailyAttendances($school, $classrooms);
            }
        });
    }

    private function roles(): array
    {
        return collect(['school_admin', 'principal', 'teacher', 'student'])
            ->mapWithKeys(fn ($role) => [
                $role => Role::firstOrCreate([
                    'name' => $role,
                    'guard_name' => 'web',
                ]),
            ])
            ->all();
    }

    private function seedSchool(array $data, Role $schoolAdminRole, Role $principalRole): School
    {
        $superAdmin = User::where('email', 'admin@mail.com')->first();

        $school = School::updateOrCreate([
            'npsn' => $data['npsn'],
        ], [
            'name' => $data['name'],
            'level' => $data['level'],
            'address' => $data['address'],
            'phone' => $data['phone'],
            'email' => $data['email'],
            'status' => 'active',
            'approved_at' => now(),
            'approved_by' => $superAdmin?->id,
            'onboarding_completed_at' => now(),
        ]);

        $admin = User::updateOrCreate([
            'email' => $data['admin_email'],
        ], [
            'name' => $data['admin_name'],
            'password' => Hash::make(self::PASSWORD),
            'status' => 'active',
        ]);

        $admin->syncRoles(['school_admin']);

        $school->users()->syncWithoutDetaching([
            $admin->id => [
                'role_id' => $schoolAdminRole->id,
                'status' => 'active',
            ],
        ]);

        $principal = User::updateOrCreate([
            'email' => $data['principal_email'],
        ], [
            'name' => $data['principal_name'],
            'password' => Hash::make(self::PASSWORD),
            'status' => 'active',
        ]);

        $principal->syncRoles(['principal']);

        $school->users()->syncWithoutDetaching([
            $principal->id => [
                'role_id' => $principalRole->id,
                'status' => 'active',
            ],
        ]);

        return $school;
    }

    private function seedAcademicYear(School $school): AcademicYear
    {
        $school->academicYears()->update(['is_active' => false]);

        return AcademicYear::updateOrCreate([
            'school_id' => $school->id,
            'name' => self::ACADEMIC_YEAR,
        ], [
            'starts_at' => '2025-07-14',
            'ends_at' => '2026-06-20',
            'is_active' => true,
        ]);
    }

    private function seedSemesters(School $school, AcademicYear $academicYear): Semester
    {
        $semesters = [
            ['name' => 'Ganjil', 'starts_at' => '2025-07-14', 'ends_at' => '2025-12-20', 'is_active' => true],
            ['name' => 'Genap', 'starts_at' => '2026-01-05', 'ends_at' => '2026-06-20', 'is_active' => false],
        ];

        $activeSemester = null;

        foreach ($semesters as $semesterData) {
            $semester = Semester::updateOrCreate([
                'school_id' => $school->id,
                'academic_year_id' => $academicYear->id,
                'name' => $semesterData['name'],
            ], $semesterData + [
                'school_id' => $school->id,
                'academic_year_id' => $academicYear->id,
            ]);

            if ($semesterData['is_active']) {
                $activeSemester = $semester;
            }
        }

        return $activeSemester;
    }

    private function seedClasses(School $school, AcademicYear $academicYear, array $levels): array
    {
        $classes = [];

        foreach ($levels as $level) {
            $classes[$level['name']] = SchoolClass::updateOrCreate([
                'school_id' => $school->id,
                'academic_year_id' => $academicYear->id,
                'name' => $level['class_name'],
            ], [
                'level' => $level['name'],
                'is_active' => true,
            ]);
        }

        return $classes;
    }

    private function seedSubjects(School $school, array $subjects): array
    {
        $seededSubjects = [];

        foreach ($subjects as $subject) {
            $seededSubjects[] = Subject::updateOrCreate([
                'school_id' => $school->id,
                'code' => $subject['code'],
            ], [
                'name' => $subject['name'],
                'is_active' => true,
            ]);
        }

        return $seededSubjects;
    }

    private function seedRooms(School $school): array
    {
        $rooms = [
            ['name' => 'Ruang Kelas 1', 'code' => 'R-01', 'type' => 'classroom', 'capacity' => 32, 'location' => 'Gedung A Lantai 1'],
            ['name' => 'Ruang Kelas 2', 'code' => 'R-02', 'type' => 'classroom', 'capacity' => 32, 'location' => 'Gedung A Lantai 1'],
            ['name' => 'Ruang Kelas 3', 'code' => 'R-03', 'type' => 'classroom', 'capacity' => 32, 'location' => 'Gedung A Lantai 2'],
            ['name' => 'Ruang Kelas 4', 'code' => 'R-04', 'type' => 'classroom', 'capacity' => 32, 'location' => 'Gedung A Lantai 2'],
            ['name' => 'Lab Komputer', 'code' => 'LAB-KOM', 'type' => 'laboratory', 'capacity' => 30, 'location' => 'Gedung B'],
            ['name' => 'Lab IPA', 'code' => 'LAB-IPA', 'type' => 'laboratory', 'capacity' => 30, 'location' => 'Gedung B'],
            ['name' => 'Perpustakaan', 'code' => 'PERPUS', 'type' => 'library', 'capacity' => 40, 'location' => 'Gedung C'],
        ];

        $seededRooms = [];

        foreach ($rooms as $room) {
            $seededRooms[] = Room::updateOrCreate([
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

        return $seededRooms;
    }

    private function seedTeachers(School $school, array $schoolData, Role $teacherRole): array
    {
        $teachers = [];

        foreach ($this->teacherNames() as $index => $teacherData) {
            $number = $index + 1;
            $email = 'guru.'.strtolower($schoolData['level']).'.'.$number.'@mail.com';

            $user = User::updateOrCreate([
                'email' => $email,
            ], [
                'name' => $teacherData['name'].' '.$schoolData['level'],
                'password' => Hash::make(self::PASSWORD),
                'status' => 'active',
            ]);

            $user->syncRoles(['teacher']);

            $school->users()->syncWithoutDetaching([
                $user->id => [
                    'role_id' => $teacherRole->id,
                    'status' => 'active',
                ],
            ]);

            $teachers[] = Teacher::updateOrCreate([
                'school_id' => $school->id,
                'user_id' => $user->id,
            ], [
                'nip' => '1988'.$schoolData['code'].str_pad((string) $number, 2, '0', STR_PAD_LEFT).'202601'.$number,
                'nuptk' => '88'.$school->npsn.str_pad((string) $number, 2, '0', STR_PAD_LEFT),
                'employee_number' => 'G-'.$schoolData['code'].'-'.str_pad((string) $number, 3, '0', STR_PAD_LEFT),
                'gender' => $teacherData['gender'],
                'phone' => '0812'.$schoolData['code'].str_pad((string) $number, 4, '0', STR_PAD_LEFT),
                'birth_place' => $teacherData['birth_place'],
                'birth_date' => '1988-'.str_pad((string) $number, 2, '0', STR_PAD_LEFT).'-12',
                'address' => 'Alamat guru '.$number.' - '.$school->name,
                'is_active' => true,
            ]);
        }

        return $teachers;
    }

    private function seedStudents(School $school, array $schoolData, Role $studentRole): array
    {
        $studentsByEntryYear = [];

        foreach ($schoolData['levels'] as $levelIndex => $level) {
            $studentsByEntryYear[$level['entry_year']] = [];

            for ($number = 1; $number <= $schoolData['students_per_level']; $number++) {
                $sequence = ($levelIndex * $schoolData['students_per_level']) + $number;
                $gender = $sequence % 2 === 0 ? 'female' : 'male';
                $name = $this->studentName($sequence).' '.$level['name'].' '.$schoolData['level'];
                $email = 'murid.'.strtolower($schoolData['level']).'.'.$level['entry_year'].'.'.$number.'@mail.com';

                $user = User::updateOrCreate([
                    'email' => $email,
                ], [
                    'name' => $name,
                    'password' => Hash::make(self::PASSWORD),
                    'status' => 'active',
                ]);

                $user->syncRoles(['student']);

                $school->users()->syncWithoutDetaching([
                    $user->id => [
                        'role_id' => $studentRole->id,
                        'status' => 'active',
                    ],
                ]);

                $student = Student::updateOrCreate([
                    'school_id' => $school->id,
                    'user_id' => $user->id,
                ], [
                    'nis' => $level['entry_year'].$schoolData['code'].str_pad((string) $number, 3, '0', STR_PAD_LEFT),
                    'nisn' => $school->npsn.str_pad((string) $sequence, 4, '0', STR_PAD_LEFT),
                    'entry_year' => $level['entry_year'],
                    'gender' => $gender,
                    'phone' => '0822'.$schoolData['code'].str_pad((string) $sequence, 4, '0', STR_PAD_LEFT),
                    'birth_place' => $this->birthPlace($sequence),
                    'birth_date' => $this->birthDate($schoolData['level'], $level['entry_year'], $number),
                    'address' => 'Alamat murid '.$sequence.' - '.$school->name,
                    'is_active' => true,
                ]);

                $studentsByEntryYear[$level['entry_year']][] = $student;
            }
        }

        return $studentsByEntryYear;
    }

    private function seedClassrooms(
        School $school,
        AcademicYear $academicYear,
        Semester $semester,
        array $levels,
        array $schoolClasses,
        array $teachers,
        array $studentsByEntryYear
    ): array {
        $classrooms = [];

        foreach ($levels as $index => $level) {
            $classroom = Classroom::updateOrCreate([
                'school_id' => $school->id,
                'academic_year_id' => $academicYear->id,
                'semester_id' => $semester->id,
                'name' => $level['classroom_name'],
            ], [
                'school_class_id' => $schoolClasses[$level['name']]->id,
                'homeroom_teacher_id' => $teachers[$index % count($teachers)]->id,
                'capacity' => 32,
                'is_active' => true,
            ]);

            foreach ($studentsByEntryYear[$level['entry_year']] ?? [] as $student) {
                $classroom->students()->syncWithoutDetaching([
                    $student->id => ['status' => 'active'],
                ]);
            }

            $classrooms[] = $classroom;
        }

        return $classrooms;
    }

    private function seedSchedules(
        School $school,
        AcademicYear $academicYear,
        Semester $semester,
        array $classrooms,
        array $subjects,
        array $teachers,
        array $rooms
    ): void {
        $slots = [
            ['day' => 1, 'start' => '07:00', 'end' => '08:10'],
            ['day' => 1, 'start' => '08:20', 'end' => '09:30'],
            ['day' => 2, 'start' => '07:00', 'end' => '08:10'],
            ['day' => 3, 'start' => '07:00', 'end' => '08:10'],
            ['day' => 4, 'start' => '07:00', 'end' => '08:10'],
            ['day' => 5, 'start' => '07:00', 'end' => '08:10'],
        ];

        foreach ($classrooms as $classroomIndex => $classroom) {
            foreach ($slots as $slotIndex => $slot) {
                $subject = $subjects[($classroomIndex + $slotIndex) % count($subjects)];
                $teacher = $teachers[($classroomIndex + $slotIndex) % count($teachers)];
                $room = $rooms[$classroomIndex % min(4, count($rooms))];

                Schedule::updateOrCreate([
                    'school_id' => $school->id,
                    'academic_year_id' => $academicYear->id,
                    'semester_id' => $semester->id,
                    'classroom_id' => $classroom->id,
                    'day_of_week' => $slot['day'],
                    'starts_at' => $slot['start'],
                ], [
                    'subject_id' => $subject->id,
                    'teacher_id' => $teacher->id,
                    'room_id' => $room->id,
                    'ends_at' => $slot['end'],
                    'room' => $room->name,
                    'notes' => null,
                    'is_active' => true,
                ]);
            }
        }
    }

    private function seedDailyAttendances(School $school, array $classrooms): void
    {
        $statusPool = [
            'present', 'present', 'present', 'present', 'present',
            'present', 'present', 'present', 'late', 'sick',
            'permit', 'absent',
        ];

        foreach ($classrooms as $classroomIndex => $classroom) {
            $students = $classroom->students()
                ->wherePivot('status', 'active')
                ->where('students.is_active', true)
                ->orderBy('students.id')
                ->get();

            for ($day = 1; $day <= 31; $day++) {
                $attendanceDate = '2026-05-'.str_pad((string) $day, 2, '0', STR_PAD_LEFT);

                $session = AttendanceSession::updateOrCreate([
                    'school_id' => $school->id,
                    'classroom_id' => $classroom->id,
                    'attendance_date' => $attendanceDate,
                    'type' => 'daily',
                    'schedule_id' => null,
                ], [
                    'teacher_id' => $classroom->homeroom_teacher_id,
                    'subject_id' => null,
                    'starts_at' => null,
                    'ends_at' => null,
                    'status' => 'submitted',
                    'notes' => 'Data demo presensi harian bulan Mei 2026.',
                ]);

                foreach ($students as $studentIndex => $student) {
                    $statusIndex = ($classroomIndex + $studentIndex + $day) % count($statusPool);
                    $status = $statusPool[$statusIndex];
                    $checkedAt = $status
                        ? $attendanceDate.' '.str_pad((string) (7 + (($studentIndex + $day) % 2)), 2, '0', STR_PAD_LEFT).':'.str_pad((string) (5 + (($studentIndex * 3 + $day) % 45)), 2, '0', STR_PAD_LEFT).':00'
                        : null;

                    AttendanceRecord::updateOrCreate([
                        'attendance_session_id' => $session->id,
                        'student_id' => $student->id,
                    ], [
                        'status' => $status,
                        'checked_at' => $checkedAt,
                        'notes' => $status === 'present' ? null : $this->attendanceNote($status),
                    ]);
                }
            }
        }
    }

    private function schools(): array
    {
        return [
            [
                'code' => '01',
                'name' => 'SD Nusantara 01',
                'npsn' => '10000001',
                'level' => 'SD',
                'address' => 'Jl. Melati No. 1, Bandung',
                'phone' => '0221000001',
                'email' => 'sd.nusantara01@mail.com',
                'admin_name' => 'Admin SD Nusantara 01',
                'admin_email' => 'admin.sd.nusantara01@mail.com',
                'principal_name' => 'Kepala SD Nusantara 01',
                'principal_email' => 'principal.sd.nusantara01@mail.com',
                'students_per_level' => 6,
                'levels' => [
                    ['name' => 'I', 'class_name' => 'Kelas I', 'classroom_name' => 'I-A', 'entry_year' => 2025],
                    ['name' => 'II', 'class_name' => 'Kelas II', 'classroom_name' => 'II-A', 'entry_year' => 2024],
                    ['name' => 'III', 'class_name' => 'Kelas III', 'classroom_name' => 'III-A', 'entry_year' => 2023],
                    ['name' => 'IV', 'class_name' => 'Kelas IV', 'classroom_name' => 'IV-A', 'entry_year' => 2022],
                    ['name' => 'V', 'class_name' => 'Kelas V', 'classroom_name' => 'V-A', 'entry_year' => 2021],
                    ['name' => 'VI', 'class_name' => 'Kelas VI', 'classroom_name' => 'VI-A', 'entry_year' => 2020],
                ],
                'subjects' => $this->sdSubjects(),
            ],
            [
                'code' => '02',
                'name' => 'SMP Harapan Bangsa',
                'npsn' => '10000002',
                'level' => 'SMP',
                'address' => 'Jl. Pendidikan No. 2, Jakarta',
                'phone' => '0211000002',
                'email' => 'smp.harapanbangsa@mail.com',
                'admin_name' => 'Admin SMP Harapan Bangsa',
                'admin_email' => 'admin.smp.harapanbangsa@mail.com',
                'principal_name' => 'Kepala SMP Harapan Bangsa',
                'principal_email' => 'principal.smp.harapanbangsa@mail.com',
                'students_per_level' => 8,
                'levels' => [
                    ['name' => 'VII', 'class_name' => 'Kelas VII', 'classroom_name' => 'VII-A', 'entry_year' => 2025],
                    ['name' => 'VIII', 'class_name' => 'Kelas VIII', 'classroom_name' => 'VIII-A', 'entry_year' => 2024],
                    ['name' => 'IX', 'class_name' => 'Kelas IX', 'classroom_name' => 'IX-A', 'entry_year' => 2023],
                ],
                'subjects' => $this->smpSubjects(),
            ],
            [
                'code' => '03',
                'name' => 'SMA Cendekia Mandiri',
                'npsn' => '10000003',
                'level' => 'SMA',
                'address' => 'Jl. Cendekia No. 3, Yogyakarta',
                'phone' => '02741000003',
                'email' => 'sma.cendekiamandiri@mail.com',
                'admin_name' => 'Admin SMA Cendekia Mandiri',
                'admin_email' => 'admin.sma.cendekiamandiri@mail.com',
                'principal_name' => 'Kepala SMA Cendekia Mandiri',
                'principal_email' => 'principal.sma.cendekiamandiri@mail.com',
                'students_per_level' => 8,
                'levels' => [
                    ['name' => 'X', 'class_name' => 'Kelas X', 'classroom_name' => 'X-A', 'entry_year' => 2025],
                    ['name' => 'XI', 'class_name' => 'Kelas XI', 'classroom_name' => 'XI-A', 'entry_year' => 2024],
                    ['name' => 'XII', 'class_name' => 'Kelas XII', 'classroom_name' => 'XII-A', 'entry_year' => 2023],
                ],
                'subjects' => $this->smaSubjects(),
            ],
        ];
    }

    private function teacherNames(): array
    {
        return [
            ['name' => 'Budi Santoso', 'gender' => 'male', 'birth_place' => 'Bandung'],
            ['name' => 'Siti Aminah', 'gender' => 'female', 'birth_place' => 'Jakarta'],
            ['name' => 'Agus Pratama', 'gender' => 'male', 'birth_place' => 'Yogyakarta'],
            ['name' => 'Dewi Lestari', 'gender' => 'female', 'birth_place' => 'Semarang'],
            ['name' => 'Rina Kurniawati', 'gender' => 'female', 'birth_place' => 'Surabaya'],
            ['name' => 'Hendra Wijaya', 'gender' => 'male', 'birth_place' => 'Malang'],
        ];
    }

    private function sdSubjects(): array
    {
        return [
            ['name' => 'Pendidikan Agama', 'code' => 'PAI'],
            ['name' => 'Pendidikan Pancasila', 'code' => 'PPKN'],
            ['name' => 'Bahasa Indonesia', 'code' => 'BIN'],
            ['name' => 'Matematika', 'code' => 'MTK'],
            ['name' => 'Ilmu Pengetahuan Alam dan Sosial', 'code' => 'IPAS'],
            ['name' => 'Pendidikan Jasmani', 'code' => 'PJOK'],
            ['name' => 'Seni Budaya', 'code' => 'SBDP'],
            ['name' => 'Bahasa Inggris', 'code' => 'BIG'],
        ];
    }

    private function smpSubjects(): array
    {
        return [
            ['name' => 'Pendidikan Agama', 'code' => 'PAI'],
            ['name' => 'Pendidikan Pancasila', 'code' => 'PPKN'],
            ['name' => 'Bahasa Indonesia', 'code' => 'BIN'],
            ['name' => 'Matematika', 'code' => 'MTK'],
            ['name' => 'Ilmu Pengetahuan Alam', 'code' => 'IPA'],
            ['name' => 'Ilmu Pengetahuan Sosial', 'code' => 'IPS'],
            ['name' => 'Bahasa Inggris', 'code' => 'BIG'],
            ['name' => 'Informatika', 'code' => 'INF'],
        ];
    }

    private function smaSubjects(): array
    {
        return [
            ['name' => 'Pendidikan Agama', 'code' => 'PAI'],
            ['name' => 'Pendidikan Pancasila', 'code' => 'PPKN'],
            ['name' => 'Bahasa Indonesia', 'code' => 'BIN'],
            ['name' => 'Matematika', 'code' => 'MTK'],
            ['name' => 'Fisika', 'code' => 'FIS'],
            ['name' => 'Kimia', 'code' => 'KIM'],
            ['name' => 'Biologi', 'code' => 'BIO'],
            ['name' => 'Bahasa Inggris', 'code' => 'BIG'],
        ];
    }

    private function studentName(int $number): string
    {
        $firstNames = ['Ahmad', 'Siti', 'Bima', 'Dewi', 'Rizky', 'Nadia', 'Fajar', 'Putri'];
        $lastNames = ['Pratama', 'Lestari', 'Saputra', 'Anggraini', 'Maulana', 'Permata', 'Ramadhan', 'Safitri'];

        return $firstNames[($number - 1) % count($firstNames)].' '.$lastNames[($number - 1) % count($lastNames)];
    }

    private function birthPlace(int $number): string
    {
        $places = ['Bandung', 'Jakarta', 'Yogyakarta', 'Semarang', 'Surabaya', 'Malang'];

        return $places[($number - 1) % count($places)];
    }

    private function birthDate(string $level, int $entryYear, int $number): string
    {
        $ageAtEntry = $level === 'SD' ? 6 : 12;
        $birthYear = $entryYear - $ageAtEntry;

        return $birthYear.'-'.str_pad((string) (($number % 12) + 1), 2, '0', STR_PAD_LEFT).'-'.str_pad((string) (($number % 27) + 1), 2, '0', STR_PAD_LEFT);
    }

    private function attendanceNote(string $status): ?string
    {
        return [
            'sick' => 'Sakit.',
            'permit' => 'Izin keluarga.',
            'absent' => 'Tanpa keterangan.',
            'late' => 'Datang terlambat.',
        ][$status] ?? null;
    }
}
