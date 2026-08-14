@extends('layouts.app')

@php
    $statusLabels = [
        'hadir' => 'Hadir',
        'terlambat' => 'Terlambat',
        'pulang' => 'Pulang',
        'pulang_cepat' => 'Pulang Cepat',
        'di_luar_area' => 'Di Luar Area',
        'perlu_verifikasi' => 'Perlu Verifikasi',
    ];
@endphp

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 py-4 mb-2">
        <div>
            <h4 class="mb-1">Laporan Presensi Guru</h4>
            <p class="text-muted mb-0">{{ $school->name }} - rekap presensi guru berdasarkan tanggal.</p>
        </div>
        <a href="{{ route('dashboard') }}" class="btn btn-label-secondary">Kembali</a>
    </div>

    <div class="card">
        <div class="card-body">
            <form method="GET" action="{{ route('teacher-attendances.report') }}" class="row g-3 align-items-end mb-4">
                <div class="col-md-4">
                    <label class="form-label" for="date">Tanggal</label>
                    <input type="date" class="form-control" id="date" name="date" value="{{ $date }}">
                </div>
                <div class="col-md-5">
                    <label class="form-label" for="teacher_id">Guru</label>
                    <select class="form-select" id="teacher_id" name="teacher_id">
                        <option value="">Semua guru</option>
                        @foreach($teachers as $teacher)
                            <option value="{{ $teacher->id }}" @selected($teacherId === $teacher->id)>{{ $teacher->user?->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <button class="btn btn-primary w-100" type="submit">Filter</button>
                </div>
            </form>

            <div class="table-responsive text-nowrap">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Guru</th>
                            <th>Datang</th>
                            <th>Pulang</th>
                            <th>Lokasi</th>
                            <th>Selfie</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($records as $record)
                            <tr>
                                <td>
                                    <strong>{{ $record->teacher?->user?->name }}</strong>
                                    <div class="text-muted small">{{ $record->attendance_date->format('d-m-Y') }}</div>
                                </td>
                                <td>{{ $record->check_in_at?->format('H:i') ?? '-' }} <div class="text-muted small">{{ $statusLabels[$record->check_in_status] ?? '-' }}</div></td>
                                <td>{{ $record->check_out_at?->format('H:i') ?? '-' }} <div class="text-muted small">{{ $statusLabels[$record->check_out_status] ?? '-' }}</div></td>
                                <td>
                                    <div>Datang: {{ $record->check_in_distance_meters !== null ? $record->check_in_distance_meters.' m' : '-' }}</div>
                                    <div class="text-muted small">Pulang: {{ $record->check_out_distance_meters !== null ? $record->check_out_distance_meters.' m' : '-' }}</div>
                                </td>
                                <td>
                                    @if($record->check_in_photo_path)
                                        <a href="{{ \App\Support\SchoolFileStorage::url($record->check_in_photo_path) }}" target="_blank">Datang</a>
                                    @endif
                                    @if($record->check_out_photo_path)
                                        <span class="mx-1">|</span><a href="{{ \App\Support\SchoolFileStorage::url($record->check_out_photo_path) }}" target="_blank">Pulang</a>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-5">Belum ada data presensi guru.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <x-table-pagination :paginator="$records" label="presensi guru" />
        </div>
    </div>
</div>
@endsection
