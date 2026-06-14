@extends('layouts.app')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 py-4 mb-2">
        <div>
            <h4 class="mb-1">Dashboard Guru</h4>
            <p class="text-muted mb-0">
                Selamat datang, {{ Auth::user()->name }}. Pantau jadwal mengajar dan presensi dari satu tempat.
            </p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('teacher-schedules.index') }}" class="btn btn-label-primary">
                <i class="bx bx-calendar-event me-1"></i> Jadwal Saya
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
    @elseif(! $teacher)
        <div class="alert alert-warning">
            Akun guru Anda belum terhubung ke data guru aktif. Hubungi admin sekolah untuk mengaitkan akun dengan data guru.
        </div>
    @else
        <div class="row g-4 mb-4">
            <div class="col-xl-8">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-4">
                            <div>
                                <span class="badge bg-label-primary mb-3">Guru</span>
                                <h3 class="mb-2">{{ Auth::user()->name }}</h3>
                                <div class="text-muted">{{ $school->name }}</div>
                                <div class="d-flex flex-wrap gap-2 mt-3">
                                    <span class="badge bg-label-secondary">NIP: {{ $teacher->nip ?: '-' }}</span>
                                    <span class="badge bg-label-secondary">NUPTK: {{ $teacher->nuptk ?: '-' }}</span>
                                    <span class="badge bg-label-success">Aktif</span>
                                </div>
                            </div>
                            <div class="text-lg-end">
                                <div class="text-muted small">Jadwal Hari Ini</div>
                                <h2 class="mb-0">{{ $todaySchedules->count() }}</h2>
                                <div class="text-muted small">{{ now()->translatedFormat('l, d M Y') }}</div>
                            </div>
                        </div>

                        <div class="row g-3 mt-4">
                            <div class="col-sm-6 col-lg-3">
                                <a href="{{ route('teacher-schedules.index') }}" class="text-decoration-none">
                                    <div class="border rounded p-3 h-100">
                                        <div class="d-flex align-items-center justify-content-between">
                                            <span class="avatar rounded bg-label-primary"><i class="bx bx-calendar-event"></i></span>
                                            <h4 class="mb-0">{{ $todaySchedules->count() }}</h4>
                                        </div>
                                        <div class="text-muted small mt-3">Mengajar Hari Ini</div>
                                    </div>
                                </a>
                            </div>
                            <div class="col-sm-6 col-lg-3">
                                <a href="{{ route('attendances.daily') }}" class="text-decoration-none">
                                    <div class="border rounded p-3 h-100">
                                        <div class="d-flex align-items-center justify-content-between">
                                            <span class="avatar rounded bg-label-info"><i class="bx bx-group"></i></span>
                                            <h4 class="mb-0">{{ $homeroomClassrooms->count() }}</h4>
                                        </div>
                                        <div class="text-muted small mt-3">Kelas Wali</div>
                                    </div>
                                </a>
                            </div>
                            <div class="col-sm-6 col-lg-3">
                                <a href="{{ route('attendances.index') }}" class="text-decoration-none">
                                    <div class="border rounded p-3 h-100">
                                        <div class="d-flex align-items-center justify-content-between">
                                            <span class="avatar rounded bg-label-success"><i class="bx bx-check-circle"></i></span>
                                            <h4 class="mb-0">{{ $attendanceSummary['today_submitted'] }}</h4>
                                        </div>
                                        <div class="text-muted small mt-3">Submitted Hari Ini</div>
                                    </div>
                                </a>
                            </div>
                            <div class="col-sm-6 col-lg-3">
                                <a href="{{ route('attendances.index') }}" class="text-decoration-none">
                                    <div class="border rounded p-3 h-100">
                                        <div class="d-flex align-items-center justify-content-between">
                                            <span class="avatar rounded bg-label-warning"><i class="bx bx-edit"></i></span>
                                            <h4 class="mb-0">{{ $attendanceSummary['today_draft'] }}</h4>
                                        </div>
                                        <div class="text-muted small mt-3">Draft Hari Ini</div>
                                    </div>
                                </a>
                            </div>
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
                            <i class="bx bx-calendar-check me-1"></i> Presensi Harian
                        </a>
                        <a href="{{ route('attendances.schedule') }}" class="btn btn-label-info text-start">
                            <i class="bx bx-time-five me-1"></i> Presensi Per Jadwal
                        </a>
                        <a href="{{ route('teacher-schedules.index') }}" class="btn btn-label-success text-start">
                            <i class="bx bx-calendar-event me-1"></i> Lihat Jadwal Mengajar
                        </a>
                        <a href="{{ route('attendances.report.daily') }}" class="btn btn-label-secondary text-start">
                            <i class="bx bx-file me-1"></i> Report Presensi Harian
                        </a>
                        <a href="{{ route('attendances.report.schedule') }}" class="btn btn-label-secondary text-start">
                            <i class="bx bx-file me-1"></i> Report Presensi Per Jadwal
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-xl-7">
                <div class="card h-100">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <h5 class="mb-0">Jadwal Mengajar Hari Ini</h5>
                        <a href="{{ route('teacher-schedules.index') }}" class="btn btn-sm btn-label-primary">Semua Jadwal</a>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive text-nowrap">
                            <table class="table table-hover align-middle">
                                <thead>
                                    <tr>
                                        <th>Jam</th>
                                        <th>Rombel</th>
                                        <th>Mata Pelajaran</th>
                                        <th>Ruangan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($todaySchedules as $schedule)
                                        <tr>
                                            <td>{{ substr((string) $schedule->starts_at, 0, 5) }} - {{ substr((string) $schedule->ends_at, 0, 5) }}</td>
                                            <td>{{ $schedule->classroom?->name ?? '-' }}</td>
                                            <td>{{ $schedule->subject?->name ?? '-' }}</td>
                                            <td>{{ $schedule->physicalRoom?->name ?: ($schedule->room ?: '-') }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center text-muted py-4">Tidak ada jadwal mengajar hari ini.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-5">
                <div class="card h-100">
                    <div class="card-header">
                        <h5 class="mb-0">Kelas Wali</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex flex-column gap-3">
                            @forelse($homeroomClassrooms as $classroom)
                                <div class="border rounded p-3">
                                    <div class="d-flex align-items-start justify-content-between gap-3">
                                        <div>
                                            <h6 class="mb-1">{{ $classroom->name }}</h6>
                                            <div class="text-muted small">
                                                {{ $classroom->academicYear?->name ?? '-' }} · {{ $classroom->semester?->name ?? '-' }}
                                            </div>
                                        </div>
                                        <span class="badge bg-label-primary">{{ $classroom->students_count }} murid</span>
                                    </div>
                                    <div class="mt-3">
                                        <a href="{{ route('attendances.daily') }}" class="btn btn-sm btn-label-primary">
                                            <i class="bx bx-calendar-check me-1"></i> Presensi Harian
                                        </a>
                                    </div>
                                </div>
                            @empty
                                <div class="text-center text-muted py-4">Anda belum menjadi wali kelas pada rombel aktif.</div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <h5 class="mb-0">Aktivitas Presensi Terbaru</h5>
                        <a href="{{ route('attendances.report.daily') }}" class="btn btn-sm btn-label-secondary">Report Harian</a>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive text-nowrap">
                            <table class="table table-hover align-middle">
                                <thead>
                                    <tr>
                                        <th>Tanggal</th>
                                        <th>Jenis</th>
                                        <th>Rombel</th>
                                        <th>Mata Pelajaran</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($recentAttendanceSessions as $session)
                                        <tr>
                                            <td>{{ $session->attendance_date?->format('d M Y') }}</td>
                                            <td>{{ $session->type === 'schedule' ? 'Per Jadwal' : 'Harian' }}</td>
                                            <td>{{ $session->classroom?->name ?? '-' }}</td>
                                            <td>{{ $session->subject?->name ?? 'Presensi Harian' }}</td>
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
        </div>
    @endif
</div>
@endsection
