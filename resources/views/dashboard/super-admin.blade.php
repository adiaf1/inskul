@extends('layouts.app')

@section('content')
@php
    $levelLabels = [
        'sd' => 'SD',
        'smp' => 'SMP',
        'sma' => 'SMA',
        'smk' => 'SMK',
    ];

    $statusLabels = [
        'pending' => ['label' => 'Menunggu Approval', 'class' => 'warning'],
        'active' => ['label' => 'Aktif', 'class' => 'success'],
        'inactive' => ['label' => 'Tidak Aktif', 'class' => 'secondary'],
        'rejected' => ['label' => 'Ditolak', 'class' => 'danger'],
    ];
@endphp

<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 py-4 mb-2">
        <div>
            <h4 class="mb-1">Dashboard Super Admin</h4>
            <p class="text-muted mb-0">Pantau sekolah, pengguna, dan pengajuan aktivasi dari satu halaman.</p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            @include('dashboard.partials.pwa-install-button')
            <a href="{{ route('schools.index', ['status' => 'pending']) }}" class="btn btn-warning">
                <i class="bx bx-bell me-1"></i> Approval Sekolah
                @if($summary['schools_pending'] > 0)
                    <span class="badge bg-white text-warning ms-1">{{ $summary['schools_pending'] }}</span>
                @endif
            </a>
            <a href="{{ route('users.index') }}" class="btn btn-label-primary">
                <i class="bx bx-user me-1"></i> Kelola User
            </a>
            <a href="{{ route('view-as.index', [], false) }}" class="btn btn-label-secondary">
                <i class="bx bx-show me-1"></i> View Sistem
            </a>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-xl-8">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-4">
                        <div>
                            <span class="badge bg-label-primary mb-3">Super Admin</span>
                            <h3 class="mb-2">Selamat datang, {{ Auth::user()->name }}</h3>
                            <p class="text-muted mb-0">
                                Fokus utama hari ini: tinjau sekolah baru, pastikan akun aktif sesuai sekolahnya, dan pantau kesiapan data utama.
                            </p>
                        </div>
                        <div class="text-lg-end">
                            <div class="text-muted small">Sekolah Terdaftar</div>
                            <h2 class="mb-0">{{ $summary['schools_total'] }}</h2>
                            <div class="text-muted small">{{ $summary['schools_active'] }} sekolah aktif</div>
                        </div>
                    </div>

                    <div class="row g-3 mt-4">
                        <div class="col-sm-6 col-lg-3">
                            <a href="{{ route('schools.index', ['status' => 'pending']) }}" class="text-decoration-none">
                                <div class="border rounded p-3 h-100">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <span class="avatar rounded bg-label-warning"><i class="bx bx-time-five"></i></span>
                                        <h4 class="mb-0">{{ $summary['schools_pending'] }}</h4>
                                    </div>
                                    <div class="text-muted small mt-3">Menunggu Approval</div>
                                </div>
                            </a>
                        </div>
                        <div class="col-sm-6 col-lg-3">
                            <a href="{{ route('schools.index', ['status' => 'active']) }}" class="text-decoration-none">
                                <div class="border rounded p-3 h-100">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <span class="avatar rounded bg-label-success"><i class="bx bx-building-house"></i></span>
                                        <h4 class="mb-0">{{ $summary['schools_active'] }}</h4>
                                    </div>
                                    <div class="text-muted small mt-3">Sekolah Aktif</div>
                                </div>
                            </a>
                        </div>
                        <div class="col-sm-6 col-lg-3">
                            <a href="{{ route('users.index') }}" class="text-decoration-none">
                                <div class="border rounded p-3 h-100">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <span class="avatar rounded bg-label-primary"><i class="bx bx-user"></i></span>
                                        <h4 class="mb-0">{{ $summary['users_total'] }}</h4>
                                    </div>
                                    <div class="text-muted small mt-3">Total User</div>
                                </div>
                            </a>
                        </div>
                        <div class="col-sm-6 col-lg-3">
                            <div class="border rounded p-3 h-100">
                                <div class="d-flex align-items-center justify-content-between">
                                    <span class="avatar rounded bg-label-info"><i class="bx bx-calendar-check"></i></span>
                                    <h4 class="mb-0">{{ $summary['attendance_today'] }}</h4>
                                </div>
                                <div class="text-muted small mt-3">Presensi Hari Ini</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4">
            <div class="card h-100">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h5 class="mb-0">Komposisi Sekolah</h5>
                    <a href="{{ route('schools.index') }}" class="btn btn-sm btn-label-primary">Lihat</a>
                </div>
                <div class="card-body">
                    <div class="d-flex flex-column gap-3">
                        <div>
                            <div class="d-flex justify-content-between mb-1">
                                <span class="text-muted">Aktif</span>
                                <strong>{{ $summary['schools_active'] }}</strong>
                            </div>
                            <div class="progress" style="height: 8px;">
                                <div class="progress-bar bg-success" style="width: {{ $summary['schools_total'] > 0 ? ($summary['schools_active'] / $summary['schools_total']) * 100 : 0 }}%;"></div>
                            </div>
                        </div>
                        <div>
                            <div class="d-flex justify-content-between mb-1">
                                <span class="text-muted">Pending</span>
                                <strong>{{ $summary['schools_pending'] }}</strong>
                            </div>
                            <div class="progress" style="height: 8px;">
                                <div class="progress-bar bg-warning" style="width: {{ $summary['schools_total'] > 0 ? ($summary['schools_pending'] / $summary['schools_total']) * 100 : 0 }}%;"></div>
                            </div>
                        </div>
                        <div class="d-flex flex-wrap gap-2 pt-2">
                            @forelse($schoolLevelCounts as $level => $total)
                                <span class="badge bg-label-secondary">{{ $levelLabels[strtolower($level)] ?? strtoupper($level) }}: {{ $total }}</span>
                            @empty
                                <span class="text-muted small">Belum ada data jenjang sekolah.</span>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-md-6 col-xl-3">
            <div class="card h-100">
                <div class="card-body">
                    <span class="avatar rounded bg-label-primary mb-3"><i class="bx bx-user-check"></i></span>
                    <div class="text-muted small">User Aktif</div>
                    <h4 class="mb-0">{{ $summary['users_active'] }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="card h-100">
                <div class="card-body">
                    <span class="avatar rounded bg-label-info mb-3"><i class="bx bx-chalkboard"></i></span>
                    <div class="text-muted small">Guru Terdaftar</div>
                    <h4 class="mb-0">{{ $summary['teachers_total'] }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="card h-100">
                <div class="card-body">
                    <span class="avatar rounded bg-label-success mb-3"><i class="bx bx-group"></i></span>
                    <div class="text-muted small">Murid Terdaftar</div>
                    <h4 class="mb-0">{{ $summary['students_total'] }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="card h-100">
                <div class="card-body">
                    <span class="avatar rounded bg-label-warning mb-3"><i class="bx bx-check-circle"></i></span>
                    <div class="text-muted small">Presensi Submitted Hari Ini</div>
                    <h4 class="mb-0">{{ $summary['attendance_submitted_today'] }}</h4>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-xl-7">
            <div class="card h-100">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h5 class="mb-0">Pengajuan Sekolah Terbaru</h5>
                    <a href="{{ route('schools.index', ['status' => 'pending']) }}" class="btn btn-sm btn-label-warning">Semua Pending</a>
                </div>
                <div class="card-body">
                    <div class="table-responsive text-nowrap">
                        <table class="table table-hover align-middle">
                            <thead>
                                <tr>
                                    <th>Sekolah</th>
                                    <th>Admin</th>
                                    <th>Status</th>
                                    <th class="text-end">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($pendingSchools as $school)
                                    @php
                                        $admin = $school->users->first(fn ($schoolUser) => $schoolUser->roles->contains('name', 'school_admin'));
                                        $status = $statusLabels[$school->status] ?? ['label' => ucfirst($school->status), 'class' => 'secondary'];
                                    @endphp
                                    <tr>
                                        <td>
                                            <div class="fw-medium">{{ $school->name }}</div>
                                            <div class="text-muted small">NPSN: {{ $school->npsn ?: '-' }} · {{ strtoupper($school->level ?: '-') }}</div>
                                        </td>
                                        <td>
                                            <div>{{ $admin?->name ?? '-' }}</div>
                                            <div class="text-muted small">{{ $admin?->email ?? '-' }}</div>
                                        </td>
                                        <td><span class="badge bg-label-{{ $status['class'] }}">{{ $status['label'] }}</span></td>
                                        <td class="text-end">
                                            <div class="d-inline-flex gap-1">
                                                <form action="{{ route('schools.approve', $school) }}" method="POST">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="btn btn-sm btn-icon btn-label-success" title="Setujui">
                                                        <i class="bx bx-check"></i>
                                                    </button>
                                                </form>
                                                <form action="{{ route('schools.reject', $school) }}" method="POST">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="btn btn-sm btn-icon btn-label-danger" title="Tolak">
                                                        <i class="bx bx-x"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-4">Tidak ada pengajuan sekolah yang menunggu approval.</td>
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
                    <h5 class="mb-0">Sekolah Aktif Terbaru</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex flex-column gap-3">
                        @forelse($activeSchools as $school)
                            <div class="border rounded p-3">
                                <div class="d-flex align-items-start justify-content-between gap-3">
                                    <div>
                                        <h6 class="mb-1">{{ $school->name }}</h6>
                                        <div class="text-muted small">
                                            {{ strtoupper($school->level ?: '-') }} · {{ $school->approved_at?->format('d M Y') ?? 'Tanggal approval belum ada' }}
                                        </div>
                                    </div>
                                    <span class="badge bg-label-success">Aktif</span>
                                </div>
                                <div class="d-flex flex-wrap gap-2 mt-3">
                                    <span class="badge bg-label-secondary">Guru {{ $school->teachers_count }}</span>
                                    <span class="badge bg-label-secondary">Murid {{ $school->students_count }}</span>
                                    <span class="badge bg-label-secondary">Rombel {{ $school->classrooms_count }}</span>
                                    <span class="badge bg-label-secondary">Jadwal {{ $school->schedules_count }}</span>
                                </div>
                            </div>
                        @empty
                            <div class="text-center text-muted py-4">Belum ada sekolah aktif.</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
