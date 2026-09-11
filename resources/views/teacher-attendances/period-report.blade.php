@extends('layouts.app')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 py-4 mb-2">
        <div>
            <h4 class="mb-1">Rekap Presensi Guru</h4>
            <p class="text-muted mb-0">{{ $school->name }} - laporan periode presensi guru.</p>
        </div>

        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('teacher-attendances.report.period.print', request()->query()) }}" target="_blank" class="btn btn-label-secondary">
                <i class="bx bx-printer me-1"></i> Print
            </a>
            <a href="{{ route('teacher-attendances.report') }}" class="btn btn-label-secondary">
                <i class="bx bx-arrow-back me-1"></i> Kembali
            </a>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('teacher-attendances.report.period') }}" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label" for="date_from">Dari Tanggal</label>
                    <input type="date" class="form-control" id="date_from" name="date_from" value="{{ $dateFrom }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label" for="date_to">Sampai Tanggal</label>
                    <input type="date" class="form-control" id="date_to" name="date_to" value="{{ $dateTo }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label" for="teacher_id">Guru</label>
                    <select class="form-select" id="teacher_id" name="teacher_id">
                        <option value="">Semua Guru</option>
                        @foreach($teachers as $teacher)
                            <option value="{{ $teacher->id }}" @selected((string) $teacherId === (string) $teacher->id)>
                                {{ $teacher->user?->name ?? '-' }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <button class="btn btn-primary w-100" type="submit">Terapkan</button>
                </div>
            </form>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-6 col-md-3">
            <div class="card h-100">
                <div class="card-body">
                    <div class="text-muted small">Guru</div>
                    <h4 class="mb-0">{{ $totals['teacher_count'] }}</h4>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card h-100">
                <div class="card-body">
                    <div class="text-muted small">Hadir</div>
                    <h4 class="mb-0">{{ $totals['hadir'] }}</h4>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card h-100">
                <div class="card-body">
                    <div class="text-muted small">Terlambat</div>
                    <h4 class="mb-0">{{ $totals['terlambat'] }}</h4>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card h-100">
                <div class="card-body">
                    <div class="text-muted small">Belum Absen</div>
                    <h4 class="mb-0">{{ $totals['belum_absen'] }}</h4>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="table-responsive text-nowrap">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>Guru</th>
                        <th class="text-center">Hari Efektif</th>
                        <th class="text-center">Hadir</th>
                        <th class="text-center">Terlambat</th>
                        <th class="text-center">Pulang</th>
                        <th class="text-center">Pulang Cepat</th>
                        <th class="text-center">Luar Area</th>
                        <th class="text-center">Belum Absen</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($summaryRows as $row)
                        <tr>
                            <td>
                                <strong>{{ $row['teacher']->user?->name ?? '-' }}</strong>
                                <div class="text-muted small">{{ $row['teacher']->nip ?? '-' }}</div>
                            </td>
                            <td class="text-center">{{ $row['expected_days'] }}</td>
                            <td class="text-center">{{ $row['hadir'] }}</td>
                            <td class="text-center">{{ $row['terlambat'] }}</td>
                            <td class="text-center">{{ $row['pulang'] }}</td>
                            <td class="text-center">{{ $row['pulang_cepat'] }}</td>
                            <td class="text-center">{{ $row['di_luar_area'] }}</td>
                            <td class="text-center">{{ $row['belum_absen'] }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-5">Tidak ada data presensi guru pada rentang tanggal ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
