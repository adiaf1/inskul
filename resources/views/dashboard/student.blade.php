@extends('layouts.app')

@section('content')
@php
    $moduleClassAttendance = \App\Support\ModuleAccess::enabled($school, 'class_attendance');
    $moduleScheduleAttendance = \App\Support\ModuleAccess::enabled($school, 'schedule_attendance');
    $moduleAttendanceRecords = $moduleClassAttendance || $moduleScheduleAttendance;
    $statusLabels = [
        'present' => ['label' => 'Hadir', 'class' => 'primary', 'short' => 'H'],
        'sick' => ['label' => 'Sakit', 'class' => 'info', 'short' => 'S'],
        'absent' => ['label' => 'Alpa', 'class' => 'danger', 'short' => 'A'],
        'permit' => ['label' => 'Izin', 'class' => 'warning', 'short' => 'I'],
        'late' => ['label' => 'Terlambat', 'class' => 'secondary', 'short' => 'T'],
    ];
@endphp

<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 py-4 mb-2">
        <div>
            <h4 class="mb-1">Dashboard Siswa</h4>
            <p class="text-muted mb-0">Selamat datang, {{ Auth::user()->name }}. Pantau jadwal dan kehadiranmu di sini.</p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('student-schedules.index') }}" class="btn btn-primary">
                <i class="bx bx-calendar-event me-1"></i> Jadwal
            </a>
            <a href="{{ route('students.own-nametag') }}" target="_blank" class="btn btn-label-success">
                <i class="bx bx-id-card me-1"></i> Print Nametag
            </a>
            <a href="{{ route('profile.edit') }}" class="btn btn-label-primary">
                <i class="bx bx-user me-1"></i> Profil Saya
            </a>
        </div>
    </div>

    @if(! $school)
        <div class="alert alert-warning">
            Akun Anda belum terhubung ke sekolah aktif.
        </div>
    @elseif(! $student)
        <div class="alert alert-warning">
            Akun Anda belum terhubung ke data murid aktif. Hubungi admin sekolah.
        </div>
    @else
        <div class="row g-4 mb-4">
            <div class="col-xl-8">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-4">
                            <div class="d-flex align-items-center gap-3">
                                <div class="avatar avatar-xl">
                                    @if($student->photo_path)
                                        <img src="{{ \App\Support\SchoolFileStorage::url($student->photo_path) }}" alt="{{ Auth::user()->name }}" class="rounded object-fit-cover border">
                                    @else
                                        <span class="avatar-initial rounded bg-label-primary">
                                            <i class="bx bx-user fs-3"></i>
                                        </span>
                                    @endif
                                </div>
                                <div>
                                    <span class="badge bg-label-primary mb-2">Siswa</span>
                                    <h3 class="mb-1">{{ Auth::user()->name }}</h3>
                                    <div class="text-muted">{{ $school->name }}</div>
                                    <div class="d-flex flex-wrap gap-2 mt-3">
                                        <span class="badge bg-label-secondary">NIS: {{ $student->nis ?: '-' }}</span>
                                        <span class="badge bg-label-secondary">NISN: {{ $student->nisn ?: '-' }}</span>
                                        <span class="badge bg-label-secondary">Angkatan: {{ $student->entry_year ?: '-' }}</span>
                                    </div>
                                </div>
                            </div>

                            <div class="text-md-end">
                                <div class="text-muted small">Jadwal Hari Ini</div>
                                <h2 class="mb-0">{{ $todaySchedules->count() }}</h2>
                                <div class="text-muted small">{{ now()->translatedFormat('l, d M Y') }}</div>
                            </div>
                        </div>

                        @if($moduleAttendanceRecords)
                        <div class="row g-3 mt-4">
                            <div class="col-sm-6 col-lg-3">
                                <div class="border rounded p-3 h-100">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <span class="avatar rounded bg-label-primary"><i class="bx bx-check-circle"></i></span>
                                        <h4 class="mb-0">{{ $attendanceSummary['month_present'] }}</h4>
                                    </div>
                                    <div class="text-muted small mt-3">Hadir Bulan Ini</div>
                                </div>
                            </div>
                            <div class="col-sm-6 col-lg-3">
                                <div class="border rounded p-3 h-100">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <span class="avatar rounded bg-label-info"><i class="bx bx-plus-medical"></i></span>
                                        <h4 class="mb-0">{{ $attendanceSummary['month_sick'] }}</h4>
                                    </div>
                                    <div class="text-muted small mt-3">Sakit</div>
                                </div>
                            </div>
                            <div class="col-sm-6 col-lg-3">
                                <div class="border rounded p-3 h-100">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <span class="avatar rounded bg-label-warning"><i class="bx bx-envelope"></i></span>
                                        <h4 class="mb-0">{{ $attendanceSummary['month_permit'] }}</h4>
                                    </div>
                                    <div class="text-muted small mt-3">Izin</div>
                                </div>
                            </div>
                            <div class="col-sm-6 col-lg-3">
                                <div class="border rounded p-3 h-100">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <span class="avatar rounded bg-label-danger"><i class="bx bx-x-circle"></i></span>
                                        <h4 class="mb-0">{{ $attendanceSummary['month_absent'] }}</h4>
                                    </div>
                                    <div class="text-muted small mt-3">Alpa</div>
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-xl-4">
                <div class="card h-100">
                    <div class="card-header">
                        <h5 class="mb-0">Rombel Aktif</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex flex-column gap-3">
                            @forelse($activeClassrooms as $classroom)
                                <div class="border rounded p-3">
                                    <div class="d-flex align-items-start justify-content-between gap-3">
                                        <div>
                                            <h6 class="mb-1">{{ $classroom->name }}</h6>
                                            <div class="text-muted small">{{ $classroom->academicYear?->name ?? '-' }} · {{ $classroom->semester?->name ?? '-' }}</div>
                                        </div>
                                        <span class="badge bg-label-success">Aktif</span>
                                    </div>
                                    <div class="text-muted small mt-2">
                                        Wali kelas: {{ $classroom->homeroomTeacher?->user?->name ?? '-' }}
                                    </div>
                                </div>
                            @empty
                                <div class="text-center text-muted py-4">Belum ada rombel aktif.</div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-xl-7">
                <div class="card h-100">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <h5 class="mb-0">Jadwal Hari Ini</h5>
                        <a href="{{ route('student-schedules.index') }}" class="btn btn-sm btn-label-primary">Semua Jadwal</a>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive text-nowrap">
                            <table class="table table-hover align-middle">
                                <thead>
                                    <tr>
                                        <th>Jam</th>
                                        <th>Mata Pelajaran</th>
                                        <th>Guru</th>
                                        <th>Ruangan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($todaySchedules as $schedule)
                                        <tr>
                                            <td>{{ substr((string) $schedule->starts_at, 0, 5) }} - {{ substr((string) $schedule->ends_at, 0, 5) }}</td>
                                            <td>
                                                <strong>{{ $schedule->subject?->name ?? '-' }}</strong>
                                                <div class="text-muted small">{{ $schedule->classroom?->name ?? '-' }}</div>
                                            </td>
                                            <td>{{ $schedule->teacher?->user?->name ?? '-' }}</td>
                                            <td>{{ $schedule->physicalRoom?->name ?: ($schedule->room ?: '-') }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center text-muted py-4">Tidak ada jadwal hari ini.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            @if($moduleAttendanceRecords)
            <div class="col-xl-5">
                <div class="card h-100">
                    <div class="card-header">
                        <h5 class="mb-0">Presensi Hari Ini</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex flex-column gap-3">
                            @forelse($todayAttendanceRecords as $record)
                                @php
                                    $session = $record->session;
                                    $status = $record->status;
                                    $statusMeta = $statusLabels[$status] ?? ['label' => 'Belum Diisi', 'class' => 'dark', 'short' => '-'];
                                @endphp
                                <div class="border rounded p-3">
                                    <div class="d-flex align-items-start justify-content-between gap-3">
                                        <div>
                                            <h6 class="mb-1">{{ $session?->type === 'schedule' ? ($session?->subject?->name ?? 'Presensi Jadwal') : 'Presensi Per Kelas' }}</h6>
                                            <div class="text-muted small">
                                                {{ $session?->classroom?->name ?? '-' }}
                                                @if($session?->type === 'schedule')
                                                    · {{ substr((string) $session?->starts_at, 0, 5) }} - {{ substr((string) $session?->ends_at, 0, 5) }}
                                                @endif
                                            </div>
                                        </div>
                                        <span class="badge bg-label-{{ $statusMeta['class'] }}">{{ $statusMeta['label'] }}</span>
                                    </div>
                                    @if($record->checked_at)
                                        <div class="text-muted small mt-2">Dicatat: {{ $record->checked_at->format('H:i') }}</div>
                                    @endif
                                </div>
                            @empty
                                <div class="text-center text-muted py-4">Belum ada data presensi hari ini.</div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
            @endif

            @if($moduleAttendanceRecords)
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Riwayat Presensi Terbaru</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive text-nowrap">
                            <table class="table table-hover align-middle">
                                <thead>
                                    <tr>
                                        <th>Tanggal</th>
                                        <th>Jenis</th>
                                        <th>Rombel/Mapel</th>
                                        <th>Status</th>
                                        <th>Catatan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($recentAttendanceRecords as $record)
                                        @php
                                            $session = $record->session;
                                            $status = $record->status;
                                            $statusMeta = $statusLabels[$status] ?? ['label' => 'Belum Diisi', 'class' => 'dark'];
                                        @endphp
                                        <tr>
                                            <td>{{ $session?->attendance_date?->format('d M Y') ?? '-' }}</td>
                                            <td>{{ $session?->type === 'schedule' ? 'Per Jadwal' : 'Harian' }}</td>
                                            <td>
                                                <strong>{{ $session?->classroom?->name ?? '-' }}</strong>
                                                <div class="text-muted small">{{ $session?->subject?->name ?? 'Presensi Per Kelas' }}</div>
                                            </td>
                                            <td><span class="badge bg-label-{{ $statusMeta['class'] }}">{{ $statusMeta['label'] }}</span></td>
                                            <td>{{ $record->notes ?: '-' }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center text-muted py-4">Belum ada riwayat presensi.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            @endif
        </div>
    @endif
</div>
@endsection
