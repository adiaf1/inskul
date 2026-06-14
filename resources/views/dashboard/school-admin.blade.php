@extends('layouts.app')

@section('content')
@php
    $setupItems = $school ? [
        ['label' => 'Tahun Ajaran', 'count' => $school->academic_years_count, 'route' => route('academic-years.index'), 'icon' => 'bx-calendar'],
        ['label' => 'Semester', 'count' => $school->semesters_count, 'route' => route('semesters.index'), 'icon' => 'bx-calendar-check'],
        ['label' => 'Kelas', 'count' => $school->classes_count, 'route' => route('school-classes.index'), 'icon' => 'bx-building'],
        ['label' => 'Mata Pelajaran', 'count' => $school->subjects_count, 'route' => route('subjects.index'), 'icon' => 'bx-book'],
        ['label' => 'Ruangan', 'count' => $school->rooms_count, 'route' => route('rooms.index'), 'icon' => 'bx-door-open'],
        ['label' => 'Guru', 'count' => $school->teachers_count, 'route' => route('teachers.index'), 'icon' => 'bx-chalkboard'],
        ['label' => 'Murid', 'count' => $school->students_count, 'route' => route('students.index'), 'icon' => 'bx-user'],
        ['label' => 'Rombel', 'count' => $school->classrooms_count, 'route' => route('classrooms.index'), 'icon' => 'bx-group'],
        ['label' => 'Jadwal', 'count' => $school->schedules_count, 'route' => route('schedules.index'), 'icon' => 'bx-calendar-event'],
    ] : [];
    $finishedSetup = collect($setupItems)->filter(fn ($item) => (int) $item['count'] > 0)->count();
    $setupTotal = count($setupItems);
    $setupPercent = $setupTotal > 0 ? (int) round(($finishedSetup / $setupTotal) * 100) : 0;
    $unfinishedSetup = collect($setupItems)->filter(fn ($item) => (int) $item['count'] === 0);
@endphp

<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 py-4 mb-2">
        <div>
            <h4 class="mb-1">Dashboard Admin Sekolah</h4>
            <p class="text-muted mb-0">Selamat datang, {{ Auth::user()->name }}. Kelola operasional sekolah dari satu tempat.</p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('school-profile.edit') }}" class="btn btn-label-primary">
                <i class="bx bx-building-house me-1"></i> Profil Sekolah
            </a>
            <a href="{{ route('attendances.index') }}" class="btn btn-primary">
                <i class="bx bx-calendar-check me-1"></i> Presensi
            </a>
        </div>
    </div>

    @if(! $school)
        <div class="alert alert-warning">
            Akun Anda belum terhubung ke sekolah aktif.
        </div>
    @else
        <div class="row g-4 mb-4">
            <div class="col-xl-8">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-4">
                            <div class="d-flex align-items-center gap-3">
                                <div class="avatar avatar-xl">
                                    @if($school->logo_path)
                                        <img src="{{ \App\Support\SchoolFileStorage::url($school->logo_path) }}" alt="Logo {{ $school->name }}" class="rounded object-fit-contain border">
                                    @else
                                        <span class="avatar-initial rounded bg-label-primary">
                                            <i class="bx bx-building-house fs-3"></i>
                                        </span>
                                    @endif
                                </div>
                                <div>
                                    <h4 class="mb-1">{{ $school->name }}</h4>
                                    <div class="text-muted">{{ $school->address ?: 'Alamat sekolah belum diisi' }}</div>
                                    <div class="d-flex flex-wrap gap-2 mt-2">
                                        <span class="badge bg-label-primary">{{ strtoupper($school->level ?: 'Sekolah') }}</span>
                                        <span class="badge bg-label-success">Aktif</span>
                                        <span class="badge bg-label-secondary">NPSN: {{ $school->npsn ?: '-' }}</span>
                                    </div>
                                </div>
                            </div>

                            <div class="text-lg-end">
                                <div class="text-muted small">Periode Aktif</div>
                                <h6 class="mb-1">{{ $activeAcademicYear?->name ?? 'Tahun ajaran belum aktif' }}</h6>
                                <div class="text-muted small">{{ $activeSemester?->name ?? 'Semester belum aktif' }}</div>
                            </div>
                        </div>

                        <div class="row g-3 mt-4">
                            <div class="col-sm-6 col-lg-3">
                                <a href="{{ route('students.index') }}" class="text-decoration-none">
                                    <div class="border rounded p-3 h-100">
                                        <div class="d-flex align-items-center justify-content-between">
                                            <span class="avatar rounded bg-label-primary"><i class="bx bx-user"></i></span>
                                            <h4 class="mb-0">{{ $school->students_count }}</h4>
                                        </div>
                                        <div class="text-muted small mt-3">Murid Aktif</div>
                                    </div>
                                </a>
                            </div>
                            <div class="col-sm-6 col-lg-3">
                                <a href="{{ route('teachers.index') }}" class="text-decoration-none">
                                    <div class="border rounded p-3 h-100">
                                        <div class="d-flex align-items-center justify-content-between">
                                            <span class="avatar rounded bg-label-info"><i class="bx bx-chalkboard"></i></span>
                                            <h4 class="mb-0">{{ $school->teachers_count }}</h4>
                                        </div>
                                        <div class="text-muted small mt-3">Guru</div>
                                    </div>
                                </a>
                            </div>
                            <div class="col-sm-6 col-lg-3">
                                <a href="{{ route('classrooms.index') }}" class="text-decoration-none">
                                    <div class="border rounded p-3 h-100">
                                        <div class="d-flex align-items-center justify-content-between">
                                            <span class="avatar rounded bg-label-warning"><i class="bx bx-group"></i></span>
                                            <h4 class="mb-0">{{ $school->classrooms_count }}</h4>
                                        </div>
                                        <div class="text-muted small mt-3">Rombel</div>
                                    </div>
                                </a>
                            </div>
                            <div class="col-sm-6 col-lg-3">
                                <a href="{{ route('schedules.index') }}" class="text-decoration-none">
                                    <div class="border rounded p-3 h-100">
                                        <div class="d-flex align-items-center justify-content-between">
                                            <span class="avatar rounded bg-label-success"><i class="bx bx-calendar-event"></i></span>
                                            <h4 class="mb-0">{{ $school->schedules_count }}</h4>
                                        </div>
                                        <div class="text-muted small mt-3">Jadwal</div>
                                    </div>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-4">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <div>
                                <h5 class="mb-1">Kesiapan Setup</h5>
                                <div class="text-muted small">{{ $finishedSetup }} dari {{ $setupTotal }} bagian selesai</div>
                            </div>
                            <span class="badge bg-label-{{ $setupPercent === 100 ? 'success' : 'warning' }}">{{ $setupPercent }}%</span>
                        </div>
                        <div class="progress mb-3" style="height: 8px;">
                            <div class="progress-bar" role="progressbar" style="width: {{ $setupPercent }}%;" aria-valuenow="{{ $setupPercent }}" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                        @if($unfinishedSetup->isNotEmpty())
                            <div class="text-muted small mb-3">Lengkapi data berikut agar seluruh modul berjalan optimal.</div>
                            <div class="d-flex flex-column gap-2">
                                @foreach($unfinishedSetup->take(4) as $item)
                                    <a href="{{ $item['route'] }}" class="d-flex align-items-center justify-content-between border rounded px-3 py-2 text-decoration-none">
                                        <span>{{ $item['label'] }}</span>
                                        <span class="badge bg-label-primary">Isi</span>
                                    </a>
                                @endforeach
                            </div>
                        @else
                            <div class="alert alert-success mb-0">Setup sekolah sudah lengkap.</div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4 mb-4">
            <div class="col-md-6 col-xl-3">
                <div class="card h-100">
                    <div class="card-body">
                        <span class="avatar rounded bg-label-primary mb-3"><i class="bx bx-calendar-check"></i></span>
                        <div class="text-muted small">Presensi Harian Hari Ini</div>
                        <h4 class="mb-0">{{ $attendanceSummary['daily_today'] }}</h4>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xl-3">
                <div class="card h-100">
                    <div class="card-body">
                        <span class="avatar rounded bg-label-info mb-3"><i class="bx bx-time-five"></i></span>
                        <div class="text-muted small">Presensi Jadwal Hari Ini</div>
                        <h4 class="mb-0">{{ $attendanceSummary['schedule_today'] }}</h4>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xl-3">
                <div class="card h-100">
                    <div class="card-body">
                        <span class="avatar rounded bg-label-success mb-3"><i class="bx bx-check-circle"></i></span>
                        <div class="text-muted small">Submitted Hari Ini</div>
                        <h4 class="mb-0">{{ $attendanceSummary['submitted_today'] }}</h4>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xl-3">
                <div class="card h-100">
                    <div class="card-body">
                        <span class="avatar rounded bg-label-warning mb-3"><i class="bx bx-edit"></i></span>
                        <div class="text-muted small">Draft Hari Ini</div>
                        <h4 class="mb-0">{{ $attendanceSummary['draft_today'] }}</h4>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-xl-8">
                <div class="card h-100">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <h5 class="mb-0">Aktivitas Presensi Terbaru</h5>
                        <a href="{{ route('attendances.report') }}" class="btn btn-sm btn-label-primary">Report</a>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive text-nowrap">
                            <table class="table table-hover align-middle">
                                <thead>
                                    <tr>
                                        <th>Tanggal</th>
                                        <th>Jenis</th>
                                        <th>Rombel</th>
                                        <th>Mapel/Guru</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($recentAttendanceSessions as $session)
                                        <tr>
                                            <td>{{ $session->attendance_date?->format('d M Y') }}</td>
                                            <td>{{ $session->type === 'schedule' ? 'Per Jadwal' : 'Harian' }}</td>
                                            <td>{{ $session->classroom?->name ?? '-' }}</td>
                                            <td>
                                                <div>{{ $session->subject?->name ?? 'Presensi Harian' }}</div>
                                                <div class="text-muted small">{{ $session->teacher?->user?->name ?? '-' }}</div>
                                            </td>
                                            <td>
                                                <span @class([
                                                    'badge',
                                                    'bg-label-secondary' => $session->status === 'draft',
                                                    'bg-label-success' => $session->status === 'submitted',
                                                    'bg-label-dark' => $session->status === 'locked',
                                                ])>
                                                    {{ ['draft' => 'Draft', 'submitted' => 'Submitted', 'locked' => 'Dikunci'][$session->status] ?? ucfirst($session->status) }}
                                                </span>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center text-muted py-4">Belum ada aktivitas presensi.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-4">
                <div class="card h-100">
                    <div class="card-header">
                        <h5 class="mb-0">Akses Cepat</h5>
                    </div>
                    <div class="card-body d-grid gap-2">
                        <a href="{{ route('attendances.daily') }}" class="btn btn-label-primary text-start">
                            <i class="bx bx-calendar-check me-1"></i> Input Presensi Harian
                        </a>
                        <a href="{{ route('attendances.schedule') }}" class="btn btn-label-info text-start">
                            <i class="bx bx-time-five me-1"></i> Input Presensi Per Jadwal
                        </a>
                        <a href="{{ route('students.nametags') }}" class="btn btn-label-secondary text-start">
                            <i class="bx bx-id-card me-1"></i> Cetak Nametag Murid
                        </a>
                        <a href="{{ route('schedules.index') }}" class="btn btn-label-success text-start">
                            <i class="bx bx-calendar-event me-1"></i> Kelola Jadwal
                        </a>
                        <a href="{{ route('school-users.index') }}" class="btn btn-label-warning text-start">
                            <i class="bx bx-user-plus me-1"></i> Pengguna Sekolah
                        </a>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection
