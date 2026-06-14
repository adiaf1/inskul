@extends('layouts.app')

@section('content')
@php
    $statusLabels = ['' => 'Semua Status', 'unfilled' => 'Belum Diisi'] + $recordStatuses;
@endphp

<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 py-4 mb-2">
        <div>
            <h4 class="mb-1">Report Presensi Harian</h4>
            <p class="text-muted mb-0">{{ $school->name }} - laporan presensi harian murid berdasarkan rombel.</p>
        </div>

        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('attendances.report.daily.print', request()->query()) }}" target="_blank" class="btn btn-label-secondary">
                <i class="bx bx-printer me-1"></i> Print
            </a>
            <a href="{{ route('attendances.index') }}" class="btn btn-label-secondary">Kembali</a>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('attendances.report.daily') }}">
                <div class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label" for="date_from">Dari Tanggal</label>
                        <input type="date" class="form-control" id="date_from" name="date_from" value="{{ $filters['date_from'] }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label" for="date_to">Sampai Tanggal</label>
                        <input type="date" class="form-control" id="date_to" name="date_to" value="{{ $filters['date_to'] }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label" for="classroom_id">Rombel</label>
                        <select class="form-select" id="classroom_id" name="classroom_id">
                            <option value="">Semua Rombel</option>
                            @foreach($classrooms as $classroom)
                                <option value="{{ $classroom->id }}" @selected((string) $filters['classroom_id'] === (string) $classroom->id)>
                                    {{ $classroom->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label" for="status">Status</label>
                        <select class="form-select" id="status" name="status">
                            @foreach($statusLabels as $value => $label)
                                <option value="{{ $value }}" @selected($filters['status'] === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-1">
                        <div class="d-grid gap-2">
                            <button class="btn btn-primary" type="submit" title="Filter">
                                <i class="bx bx-filter-alt"></i>
                            </button>
                            <a href="{{ route('attendances.report.daily') }}" class="btn btn-label-secondary" title="Reset Filter" aria-label="Reset Filter">
                                <i class="bx bx-reset"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive text-nowrap">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Rombel</th>
                            <th>Wali Kelas</th>
                            <th>Nama</th>
                            <th>Status</th>
                            <th>Catatan</th>
                            <th class="text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($records as $record)
                            @php
                                $session = $record->session;
                                $status = $record->status;
                            @endphp
                            <tr>
                                <td>{{ $session?->attendance_date?->format('d M Y') ?? '-' }}</td>
                                <td>{{ $session?->classroom?->name ?? '-' }}</td>
                                <td>{{ $session?->teacher?->user?->name ?? '-' }}</td>
                                <td>
                                    <strong>{{ $record->student?->user?->name ?? '-' }}</strong>
                                    <div class="text-muted small">{{ $record->student?->nis ?: '-' }}{{ $record->student?->nisn ? ' / '.$record->student?->nisn : '' }}</div>
                                </td>
                                <td>
                                    @if($status)
                                        <span @class([
                                            'badge',
                                            'bg-label-primary' => $status === 'present',
                                            'bg-label-info' => $status === 'sick',
                                            'bg-label-danger' => $status === 'absent',
                                            'bg-label-warning' => $status === 'permit',
                                            'bg-label-secondary' => $status === 'late',
                                        ])>
                                            {{ $recordStatuses[$status] ?? $status }}
                                        </span>
                                    @else
                                        <span class="badge bg-label-dark">Belum Diisi</span>
                                    @endif
                                    @if($record->checked_at)
                                        <div class="text-muted small">{{ $record->checked_at->format('H:i') }}</div>
                                    @endif
                                </td>
                                <td>{{ $record->notes ?: '-' }}</td>
                                <td class="text-end">
                                    @if($session)
                                        <a href="{{ route('attendances.daily.edit', $session) }}" class="btn btn-sm btn-icon btn-label-primary" title="Buka Sesi" aria-label="Buka Sesi">
                                            <i class="bx bx-edit-alt"></i>
                                        </a>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-5">Tidak ada data presensi harian sesuai filter.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <x-table-pagination :paginator="$records" label="data presensi harian" />
        </div>
    </div>

    <div class="card mt-4">
        <div class="card-header">
            <h5 class="mb-0">Rekap Jumlah Presensi</h5>
        </div>
        <div class="card-body">
            <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
                <span class="text-muted small me-1">Keterangan:</span>
                <span class="badge bg-label-primary">H = Hadir</span>
                <span class="badge bg-label-info">S = Sakit</span>
                <span class="badge bg-label-danger">A = Alpa</span>
                <span class="badge bg-label-warning">I = Izin</span>
                <span class="badge bg-label-secondary">T = Terlambat</span>
            </div>

            <div class="table-responsive text-nowrap">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th style="width: 60px;">No</th>
                            <th>Nama</th>
                            <th class="text-center">H</th>
                            <th class="text-center">S</th>
                            <th class="text-center">A</th>
                            <th class="text-center">I</th>
                            <th class="text-center">T</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($summaryRows as $row)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>
                                    <strong>{{ $row['student']?->user?->name ?? '-' }}</strong>
                                    <div class="text-muted small">{{ $row['student']?->nis ?: '-' }}{{ $row['student']?->nisn ? ' / '.$row['student']?->nisn : '' }}</div>
                                </td>
                                <td class="text-center">{{ $row['present'] }}</td>
                                <td class="text-center">{{ $row['sick'] }}</td>
                                <td class="text-center">{{ $row['absent'] }}</td>
                                <td class="text-center">{{ $row['permit'] }}</td>
                                <td class="text-center">{{ $row['late'] }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-5">Tidak ada data rekap sesuai filter.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
