@extends('layouts.app')

@section('content')
@php
    $periodLabel = \Illuminate\Support\Carbon::parse($filters['date_from'])->format('d M Y').' - '.\Illuminate\Support\Carbon::parse($filters['date_to'])->format('d M Y');
@endphp

<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 py-4 mb-2">
        <div>
            <h4 class="mb-1">Rekap Presensi Periode</h4>
            <p class="text-muted mb-0">{{ $school->name }} - laporan hadir, sakit, izin, alpa per rombel dan wali kelas.</p>
        </div>

        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('attendances.report.period.print', request()->query()) }}" target="_blank" class="btn btn-label-secondary">
                <i class="bx bx-printer me-1"></i> Print
            </a>
            <a href="{{ route('attendances.index') }}" class="btn btn-label-secondary">
                <i class="bx bx-arrow-back me-1"></i> Kembali
            </a>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('attendances.report.period') }}">
                <div class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label" for="date_from">Dari Tanggal</label>
                        <input type="date" class="form-control" id="date_from" name="date_from" value="{{ $filters['date_from'] }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label" for="date_to">Sampai Tanggal</label>
                        <input type="date" class="form-control" id="date_to" name="date_to" value="{{ $filters['date_to'] }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label" for="classroom_id">Rombel</label>
                        <select class="form-select" id="classroom_id" name="classroom_id">
                            <option value="">Semua Rombel</option>
                            @foreach($classrooms as $classroom)
                                <option value="{{ $classroom->id }}" @selected((string) $filters['classroom_id'] === (string) $classroom->id)>
                                    {{ $classroom->name }} - {{ $classroom->homeroomTeacher?->user?->name ?? 'Tanpa wali kelas' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <div class="d-grid d-md-flex gap-2">
                            <button class="btn btn-primary" type="submit">
                                <i class="bx bx-filter-alt me-1"></i> Terapkan
                            </button>
                            <a href="{{ route('attendances.report.period') }}" class="btn btn-label-secondary" title="Reset Filter" aria-label="Reset Filter">
                                <i class="bx bx-refresh"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="alert alert-info mb-4">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
            <div>
                <strong>{{ $periodLabel }}</strong>
                <span class="d-block small">
                    Hari efektif presensi: {{ $effectiveDates->count() }} hari. Persentase dihitung dari jumlah murid aktif x hari efektif.
                </span>
            </div>
            <span class="badge bg-label-primary">{{ $selectedClassroom?->name ?? 'Semua Rombel' }}</span>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-6 col-md-3 col-xl-2">
            <div class="card h-100">
                <div class="card-body">
                    <span class="avatar rounded bg-label-secondary mb-3"><i class="bx bx-group"></i></span>
                    <div class="text-muted small">Murid</div>
                    <h4 class="mb-0">{{ $totals['student_count'] }}</h4>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3 col-xl-2">
            <div class="card h-100">
                <div class="card-body">
                    <span class="avatar rounded bg-label-success mb-3"><i class="bx bx-user-check"></i></span>
                    <div class="text-muted small">Hadir</div>
                    <h4 class="mb-0">{{ $totals['present'] }}</h4>
                    <div class="text-muted small">{{ $totals['present_percent'] }}%</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3 col-xl-2">
            <div class="card h-100">
                <div class="card-body">
                    <span class="avatar rounded bg-label-info mb-3"><i class="bx bx-plus-medical"></i></span>
                    <div class="text-muted small">Sakit</div>
                    <h4 class="mb-0">{{ $totals['sick'] }}</h4>
                    <div class="text-muted small">{{ $totals['sick_percent'] }}%</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3 col-xl-2">
            <div class="card h-100">
                <div class="card-body">
                    <span class="avatar rounded bg-label-primary mb-3"><i class="bx bx-file"></i></span>
                    <div class="text-muted small">Izin</div>
                    <h4 class="mb-0">{{ $totals['permit'] }}</h4>
                    <div class="text-muted small">{{ $totals['permit_percent'] }}%</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3 col-xl-2">
            <div class="card h-100">
                <div class="card-body">
                    <span class="avatar rounded bg-label-danger mb-3"><i class="bx bx-user-x"></i></span>
                    <div class="text-muted small">Alpa</div>
                    <h4 class="mb-0">{{ $totals['absent'] }}</h4>
                    <div class="text-muted small">{{ $totals['absent_percent'] }}%</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3 col-xl-2">
            <div class="card h-100">
                <div class="card-body">
                    <span class="avatar rounded bg-label-secondary mb-3"><i class="bx bx-help-circle"></i></span>
                    <div class="text-muted small">Belum Diproses</div>
                    <h4 class="mb-0">{{ $totals['unprocessed'] }}</h4>
                    <div class="text-muted small">{{ $totals['unprocessed_percent'] }}%</div>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">Rekap Per Rombel</h5>
        </div>
        <div class="table-responsive text-nowrap">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>Rombel</th>
                        <th>Wali Kelas</th>
                        <th class="text-center">Murid</th>
                        <th class="text-center">Target</th>
                        <th class="text-center">Hadir</th>
                        <th class="text-center">Sakit</th>
                        <th class="text-center">Izin</th>
                        <th class="text-center">Alpa</th>
                        <th class="text-center">Ket.</th>
                        <th class="text-center">Belum Diproses</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($summaryRows as $row)
                        <tr>
                            <td>
                                <strong>{{ $row['classroom']->name }}</strong>
                                <div class="text-muted small">{{ $row['classroom']->academicYear?->name ?? '-' }} / {{ $row['classroom']->semester?->name ?? '-' }}</div>
                            </td>
                            <td>{{ $row['classroom']->homeroomTeacher?->user?->name ?? '-' }}</td>
                            <td class="text-center">{{ $row['student_count'] }}</td>
                            <td class="text-center">{{ $row['target'] }}</td>
                            <td class="text-center">{{ $row['present'] }} <span class="text-muted small">({{ $row['present_percent'] }}%)</span></td>
                            <td class="text-center">{{ $row['sick'] }} <span class="text-muted small">({{ $row['sick_percent'] }}%)</span></td>
                            <td class="text-center">{{ $row['permit'] }} <span class="text-muted small">({{ $row['permit_percent'] }}%)</span></td>
                            <td class="text-center">{{ $row['absent'] }} <span class="text-muted small">({{ $row['absent_percent'] }}%)</span></td>
                            <td class="text-center">{{ $row['explained'] }} <span class="text-muted small">({{ $row['explained_percent'] }}%)</span></td>
                            <td class="text-center">{{ $row['unprocessed'] }} <span class="text-muted small">({{ $row['unprocessed_percent'] }}%)</span></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="text-center text-muted py-5">Tidak ada rombel aktif sesuai filter.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="accordion" id="periodStudentAccordion">
        @forelse($summaryRows as $row)
            <div class="card accordion-item mb-3">
                <h2 class="accordion-header" id="classroom-heading-{{ $row['classroom']->id }}">
                    <button type="button" class="accordion-button collapsed" data-bs-toggle="collapse" data-bs-target="#classroom-collapse-{{ $row['classroom']->id }}" aria-expanded="false" aria-controls="classroom-collapse-{{ $row['classroom']->id }}">
                        {{ $row['classroom']->name }} - {{ $row['classroom']->homeroomTeacher?->user?->name ?? 'Tanpa wali kelas' }}
                    </button>
                </h2>
                <div id="classroom-collapse-{{ $row['classroom']->id }}" class="accordion-collapse collapse" aria-labelledby="classroom-heading-{{ $row['classroom']->id }}" data-bs-parent="#periodStudentAccordion">
                    <div class="accordion-body">
                        <div class="table-responsive text-nowrap">
                            <table class="table table-sm table-hover align-middle">
                                <thead>
                                    <tr>
                                        <th style="width: 60px;">No</th>
                                        <th>Nama</th>
                                        <th class="text-center">Hadir</th>
                                        <th class="text-center">Sakit</th>
                                        <th class="text-center">Izin</th>
                                        <th class="text-center">Alpa</th>
                                        <th class="text-center">Ket.</th>
                                        <th class="text-center">Belum Diproses</th>
                                        <th class="text-center">Terlambat</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($row['students'] as $studentRow)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>
                                                <strong>{{ $studentRow['student']?->user?->name ?? '-' }}</strong>
                                                <div class="text-muted small">{{ $studentRow['student']?->nis ?: '-' }}{{ $studentRow['student']?->nisn ? ' / '.$studentRow['student']?->nisn : '' }}</div>
                                            </td>
                                            <td class="text-center">{{ $studentRow['present'] }} <span class="text-muted small">({{ $studentRow['present_percent'] }}%)</span></td>
                                            <td class="text-center">{{ $studentRow['sick'] }} <span class="text-muted small">({{ $studentRow['sick_percent'] }}%)</span></td>
                                            <td class="text-center">{{ $studentRow['permit'] }} <span class="text-muted small">({{ $studentRow['permit_percent'] }}%)</span></td>
                                            <td class="text-center">{{ $studentRow['absent'] }} <span class="text-muted small">({{ $studentRow['absent_percent'] }}%)</span></td>
                                            <td class="text-center">{{ $studentRow['explained'] }} <span class="text-muted small">({{ $studentRow['explained_percent'] }}%)</span></td>
                                            <td class="text-center">{{ $studentRow['unprocessed'] }} <span class="text-muted small">({{ $studentRow['unprocessed_percent'] }}%)</span></td>
                                            <td class="text-center">{{ $studentRow['late'] }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="9" class="text-center text-muted py-4">Belum ada murid aktif pada rombel ini.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="card">
                <div class="card-body text-center text-muted py-5">Tidak ada detail murid untuk ditampilkan.</div>
            </div>
        @endforelse
    </div>
</div>
@endsection
