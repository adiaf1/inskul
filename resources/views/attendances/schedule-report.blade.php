@extends('layouts.app')

@section('content')
@php
    $statusLabels = ['' => 'Semua Status', 'unfilled' => 'Belum Diisi'] + $recordStatuses;
@endphp

<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 py-4 mb-2">
        <div>
            <h4 class="mb-1">Report Presensi Per Jadwal</h4>
            <p class="text-muted mb-0">{{ $school->name }} - laporan presensi berdasarkan mata pelajaran dan jadwal.</p>
        </div>

        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('attendances.report.schedule.print', request()->query()) }}" target="_blank" class="btn btn-label-secondary">
                <i class="bx bx-printer me-1"></i> Print
            </a>
            <a href="{{ route('attendances.index') }}" class="btn btn-label-secondary">Kembali</a>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('attendances.report.schedule') }}">
                <div class="row g-3 align-items-end">
                    <div class="col-md-2">
                        <label class="form-label" for="date_from">Dari Tanggal</label>
                        <input type="date" class="form-control" id="date_from" name="date_from" value="{{ $filters['date_from'] }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label" for="date_to">Sampai Tanggal</label>
                        <input type="date" class="form-control" id="date_to" name="date_to" value="{{ $filters['date_to'] }}">
                    </div>
                    <div class="col-md-2">
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
                    <div class="col-md-3">
                        <label class="form-label" for="subject_id">Mata Pelajaran</label>
                        <select class="form-select" id="subject_id" name="subject_id">
                            <option value="">Semua Mapel</option>
                            @foreach($subjects as $subject)
                                <option value="{{ $subject->id }}" @selected((string) $filters['subject_id'] === (string) $subject->id)>
                                    {{ $subject->name }}
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
                            <a href="{{ route('attendances.report.schedule') }}" class="btn btn-label-secondary" title="Reset Filter" aria-label="Reset Filter">
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
                            <th>Mata Pelajaran</th>
                            <th>Guru</th>
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
                                <td>
                                    {{ $session?->attendance_date?->format('d M Y') ?? '-' }}
                                    <div class="text-muted small">{{ substr((string) $session?->starts_at, 0, 5) }} - {{ substr((string) $session?->ends_at, 0, 5) }}</div>
                                </td>
                                <td>{{ $session?->classroom?->name ?? '-' }}</td>
                                <td>{{ $session?->subject?->name ?? $session?->schedule?->subject?->name ?? '-' }}</td>
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
                                </td>
                                <td>{{ $record->notes ?: '-' }}</td>
                                <td class="text-end">
                                    @if($session)
                                        <a href="{{ route('attendances.schedule.edit', $session) }}" class="btn btn-sm btn-icon btn-label-primary" title="Buka Sesi" aria-label="Buka Sesi">
                                            <i class="bx bx-edit-alt"></i>
                                        </a>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-5">Tidak ada data presensi per jadwal sesuai filter.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <x-table-pagination :paginator="$records" label="data presensi per jadwal" />
        </div>
    </div>
</div>
@endsection
