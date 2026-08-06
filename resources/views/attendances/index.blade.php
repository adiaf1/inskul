@extends('layouts.app')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 py-4 mb-2">
        <div>
            <h4 class="mb-1">Presensi</h4>
            <p class="text-muted mb-0">{{ $school->name }} - kelola presensi harian, per kelas, dan per jadwal.</p>
        </div>
    </div>

    <div class="row g-4">
        @if(\App\Support\EffectiveAccess::role(request()) === 'school_admin')
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <span class="avatar rounded bg-label-primary">
                            <i class="bx bx-qr-scan"></i>
                        </span>
                        <div>
                            <h5 class="mb-1">Presensi Harian</h5>
                            <div class="text-muted small">Scan datang dan pulang tanpa memilih kelas.</div>
                        </div>
                    </div>
                    <a href="{{ route('attendances.check') }}" class="btn btn-primary">
                        Buka Scanner
                    </a>
                </div>
            </div>
        </div>
        @endif

        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <span class="avatar rounded bg-label-success">
                            <i class="bx bx-bar-chart-alt-2"></i>
                        </span>
                        <div>
                            <h5 class="mb-1">Grafik Presensi Harian</h5>
                            <div class="text-muted small">Pantau hadir, terlambat, belum hadir, dan pulang.</div>
                        </div>
                    </div>
                    <a href="{{ route('attendances.daily-dashboard') }}" class="btn btn-success">
                        Buka Grafik
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <span class="avatar rounded bg-label-primary">
                            <i class="bx bx-calendar-check"></i>
                        </span>
                        <div>
                            <h5 class="mb-1">Presensi Per Kelas</h5>
                            <div class="text-muted small">Absensi satu kali per hari berdasarkan rombel.</div>
                        </div>
                    </div>
                    <a href="{{ route('attendances.daily') }}" class="btn btn-primary">
                        Buka Presensi Per Kelas
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <span class="avatar rounded bg-label-info">
                            <i class="bx bx-time-five"></i>
                        </span>
                        <div>
                            <h5 class="mb-1">Presensi Per Jadwal</h5>
                            <div class="text-muted small">Absensi per sesi pelajaran berdasarkan jadwal.</div>
                        </div>
                    </div>
                    <a href="{{ route('attendances.schedule') }}" class="btn btn-info">
                        Buka Presensi Per Jadwal
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <span class="avatar rounded bg-label-success">
                            <i class="bx bx-bar-chart-alt-2"></i>
                        </span>
                        <div>
                            <h5 class="mb-1">Report Presensi</h5>
                            <div class="text-muted small">Lihat, filter, dan cetak hasil presensi.</div>
                        </div>
                    </div>
                    <div class="d-grid gap-2">
                        <a href="{{ route('attendances.report.daily') }}" class="btn btn-success">
                            Report Per Kelas
                        </a>
                        <a href="{{ route('attendances.report.schedule') }}" class="btn btn-label-success">
                            Report Per Jadwal
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
