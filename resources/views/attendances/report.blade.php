@extends('layouts.app')

@section('content')
@php
    $statusLabels = ['' => 'Semua Status', 'unfilled' => 'Belum Diisi'] + $recordStatuses;
    $typeLabels = ['' => 'Semua Jenis', 'daily' => 'Harian', 'schedule' => 'Per Jadwal'];
@endphp

<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 py-4 mb-2">
        <div>
            <h4 class="mb-1">Report Presensi</h4>
            <p class="text-muted mb-0">{{ $school->name }} - laporan hasil presensi murid.</p>
        </div>

        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('attendances.report.print', request()->query()) }}" target="_blank" class="btn btn-label-secondary">
                <i class="bx bx-printer me-1"></i> Print
            </a>
            <a href="{{ route('attendances.index') }}" class="btn btn-label-secondary">Kembali</a>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('attendances.report') }}">
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
                        <label class="form-label" for="type">Jenis</label>
                        <select class="form-select" id="type" name="type">
                            @foreach($typeLabels as $value => $label)
                                <option value="{{ $value }}" @selected($filters['type'] === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
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
                        <button class="btn btn-primary w-100" type="submit">
                            <i class="bx bx-filter-alt"></i>
                        </button>
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
                            <th>Jenis</th>
                            <th>Rombel</th>
                            <th>Jadwal/Mapel</th>
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
                                $editRoute = $session?->type === 'schedule'
                                    ? route('attendances.schedule.edit', $session)
                                    : route('attendances.daily.edit', $session);
                            @endphp
                            <tr>
                                <td>{{ $session?->attendance_date?->format('d M Y') ?? '-' }}</td>
                                <td>{{ $typeLabels[$session?->type ?? ''] ?? '-' }}</td>
                                <td>{{ $session?->classroom?->name ?? '-' }}</td>
                                <td>
                                    @if($session?->type === 'schedule')
                                        <strong>{{ $session->subject?->name ?? $session->schedule?->subject?->name ?? '-' }}</strong>
                                        <div class="text-muted small">{{ substr($session->starts_at, 0, 5) }} - {{ substr($session->ends_at, 0, 5) }}</div>
                                    @else
                                        <strong>Presensi Harian</strong>
                                    @endif
                                </td>
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
                                    <a href="{{ $editRoute }}" class="btn btn-sm btn-icon btn-label-primary" title="Buka Sesi" aria-label="Buka Sesi">
                                        <i class="bx bx-edit-alt"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-5">Tidak ada data presensi sesuai filter.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <x-table-pagination :paginator="$records" label="data presensi" />
        </div>
    </div>
</div>
@endsection
