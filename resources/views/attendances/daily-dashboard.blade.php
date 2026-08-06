@extends('layouts.app')

@section('content')
@php
    $selectedDateLabel = \Illuminate\Support\Carbon::parse($filters['date'])->format('d M Y');
    $canOpenAttendanceHub = in_array(\App\Support\EffectiveAccess::role(request()), ['school_admin', 'teacher'], true);
@endphp

<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 py-4 mb-2">
        <div>
            <h4 class="mb-1">Grafik Presensi Harian</h4>
            <p class="text-muted mb-0">{{ $school->name }} - ringkasan scan datang dan pulang murid.</p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('attendances.check') }}" class="btn btn-primary">
                <i class="bx bx-qr-scan me-1"></i> Scanner
            </a>
            <a href="{{ $canOpenAttendanceHub ? route('attendances.index') : route('dashboard') }}" class="btn btn-label-secondary">
                <i class="bx bx-arrow-back me-1"></i> Kembali
            </a>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('attendances.daily-dashboard') }}">
                <div class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label" for="date">Tanggal</label>
                        <input type="date" class="form-control" id="date" name="date" value="{{ $filters['date'] }}">
                    </div>
                    <div class="col-md-5">
                        <label class="form-label" for="classroom_id">Rombel</label>
                        <select class="form-select" id="classroom_id" name="classroom_id">
                            <option value="">Semua Rombel</option>
                            @foreach($classrooms as $classroom)
                                <option value="{{ $classroom->id }}" @selected($filters['classroom_id'] === $classroom->id)>
                                    {{ $classroom->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <div class="d-grid d-md-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bx bx-filter-alt me-1"></i> Terapkan
                            </button>
                            <a href="{{ route('attendances.daily-dashboard') }}" class="btn btn-label-secondary" title="Reset Filter" aria-label="Reset Filter">
                                <i class="bx bx-refresh"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div @class(['alert mb-4', 'alert-info' => $isAttendanceDay, 'alert-warning' => ! $isAttendanceDay])>
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
            <div>
                <strong>{{ $isAttendanceDay ? 'Hari presensi aktif' : 'Bukan hari presensi' }}</strong>
                <span class="d-block small">
                    Hari sekolah aktif: {{ implode(', ', $schoolAttendanceDayLabels) }}.
                    @unless($isAttendanceDay)
                        Murid yang belum scan pada tanggal ini tidak dihitung sebagai belum hadir.
                    @endunless
                </span>
            </div>
            <span class="badge {{ $isAttendanceDay ? 'bg-label-primary' : 'bg-label-warning' }}">
                {{ $selectedDateLabel }}
            </span>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-6 col-xl-2">
            <div class="card h-100">
                <div class="card-body">
                    <span class="avatar rounded bg-label-secondary mb-3"><i class="bx bx-group"></i></span>
                    <div class="text-muted small">Murid Aktif</div>
                    <h4 class="mb-0">{{ $summary['total_students'] }}</h4>
                </div>
            </div>
        </div>
        <div class="col-6 col-xl-2">
            <div class="card h-100">
                <div class="card-body">
                    <span class="avatar rounded bg-label-success mb-3"><i class="bx bx-user-check"></i></span>
                    <div class="text-muted small">Hadir</div>
                    <h4 class="mb-0">{{ $summary['present'] }}</h4>
                </div>
            </div>
        </div>
        <div class="col-6 col-xl-2">
            <div class="card h-100">
                <div class="card-body">
                    <span class="avatar rounded bg-label-danger mb-3"><i class="bx bx-user-x"></i></span>
                    <div class="text-muted small">Belum Hadir</div>
                    <h4 class="mb-0">{{ $summary['absent'] }}</h4>
                </div>
            </div>
        </div>
        <div class="col-6 col-xl-2">
            <div class="card h-100">
                <div class="card-body">
                    <span class="avatar rounded bg-label-warning mb-3"><i class="bx bx-time"></i></span>
                    <div class="text-muted small">Terlambat</div>
                    <h4 class="mb-0">{{ $summary['late'] }}</h4>
                </div>
            </div>
        </div>
        <div class="col-6 col-xl-2">
            <div class="card h-100">
                <div class="card-body">
                    <span class="avatar rounded bg-label-info mb-3"><i class="bx bx-log-out-circle"></i></span>
                    <div class="text-muted small">Sudah Pulang</div>
                    <h4 class="mb-0">{{ $summary['checked_out'] }}</h4>
                </div>
            </div>
        </div>
        <div class="col-6 col-xl-2">
            <div class="card h-100">
                <div class="card-body">
                    <span class="avatar rounded bg-label-primary mb-3"><i class="bx bx-line-chart"></i></span>
                    <div class="text-muted small">Kehadiran</div>
                    <h4 class="mb-0">{{ $summary['attendance_percent'] }}%</h4>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-lg-4">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="mb-0">Hadir vs Belum Hadir</h5>
                    <div class="text-muted small">{{ $selectedDateLabel }}</div>
                </div>
                <div class="card-body">
                    <div id="attendanceDonutChart" style="min-height: 260px;"></div>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="mb-0">Ketepatan Waktu</h5>
                    <div class="text-muted small">Berdasarkan scan datang.</div>
                </div>
                <div class="card-body">
                    <div id="punctualityBarChart" style="min-height: 260px;"></div>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="mb-0">Status Pulang</h5>
                    <div class="text-muted small">Murid yang sudah scan datang.</div>
                </div>
                <div class="card-body">
                    <div id="checkoutDonutChart" style="min-height: 260px;"></div>
                </div>
            </div>
        </div>
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Trend 7 Hari</h5>
                    <div class="text-muted small">Hadir, belum hadir, dan terlambat.</div>
                </div>
                <div class="card-body">
                    <div id="attendanceTrendChart" style="min-height: 320px;"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div>
                <h5 class="mb-0">Detail Murid</h5>
                <div class="text-muted small">
                    {{ $selectedClassroom?->name ?? 'Semua Rombel' }} - {{ $selectedDateLabel }}
                </div>
            </div>
        </div>
        <div class="table-responsive text-nowrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>Nama</th>
                        <th>Rombel</th>
                        <th>Status</th>
                        <th>Datang</th>
                        <th>Pulang</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($students as $student)
                        @php
                            $attendance = $studentAttendances->get($student->id);
                            $classroomName = $student->classrooms->first()?->name ?? '-';
                        @endphp
                        <tr>
                            <td>
                                <div class="fw-semibold">{{ $student->user?->name ?? '-' }}</div>
                                <div class="text-muted small">{{ $student->nis ?: '-' }}@if($student->nisn) / {{ $student->nisn }}@endif</div>
                            </td>
                            <td>{{ $classroomName }}</td>
                            <td>
                                @if($attendance?->check_in_at)
                                    <span class="badge bg-label-success">Hadir</span>
                                    @if($attendance->check_in_status === 'late')
                                        <span class="badge bg-label-warning">Terlambat {{ $attendance->late_minutes }} menit</span>
                                    @elseif($attendance->check_in_status === 'on_time')
                                        <span class="badge bg-label-primary">Tepat waktu</span>
                                    @endif
                                @else
                                    <span class="badge bg-label-danger">Belum Hadir</span>
                                @endif
                            </td>
                            <td>{{ $attendance?->check_in_at?->format('H:i:s') ?? '-' }}</td>
                            <td>
                                {{ $attendance?->check_out_at?->format('H:i:s') ?? '-' }}
                                @if($attendance?->check_out_status === 'early')
                                    <span class="badge bg-label-warning ms-1">Pulang cepat {{ $attendance->early_leave_minutes }} menit</span>
                                @elseif($attendance?->check_out_status === 'normal')
                                    <span class="badge bg-label-success ms-1">Normal</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">Belum ada murid aktif untuk filter ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($students->hasPages())
            <div class="card-footer">
                {{ $students->links() }}
            </div>
        @endif
    </div>
</div>

<script src="{{ asset('assets/vendor/libs/apex-charts/apexcharts.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const chartData = @json($chartData);
    const css = getComputedStyle(document.documentElement);
    const textColor = css.getPropertyValue('--bs-body-color').trim() || '#566a7f';
    const borderColor = css.getPropertyValue('--bs-border-color').trim() || '#e6e6e8';
    const cardColor = css.getPropertyValue('--bs-card-bg').trim() || '#fff';
    const colors = {
        success: css.getPropertyValue('--bs-success').trim() || '#71dd37',
        danger: css.getPropertyValue('--bs-danger').trim() || '#ff3e1d',
        warning: css.getPropertyValue('--bs-warning').trim() || '#ffab00',
        info: css.getPropertyValue('--bs-info').trim() || '#03c3ec',
        primary: css.getPropertyValue('--bs-primary').trim() || '#696cff',
        secondary: css.getPropertyValue('--bs-secondary').trim() || '#8592a3',
    };

    const baseChart = {
        chart: {
            toolbar: { show: false },
            foreColor: textColor,
            background: cardColor,
        },
        dataLabels: { enabled: true },
        legend: {
            labels: { colors: textColor },
        },
        tooltip: {
            theme: document.documentElement.getAttribute('data-bs-theme') === 'dark' ? 'dark' : 'light',
        },
    };

    const renderDonut = (selector, labels, series, chartColors) => {
        const element = document.querySelector(selector);

        if (!element || !window.ApexCharts) {
            return;
        }

        new ApexCharts(element, {
            ...baseChart,
            chart: { ...baseChart.chart, type: 'donut', height: 260 },
            labels,
            series,
            colors: chartColors,
            stroke: { width: 0 },
            plotOptions: {
                pie: {
                    donut: {
                        size: '72%',
                        labels: {
                            show: true,
                            total: {
                                show: true,
                                label: 'Total',
                                color: textColor,
                            },
                        },
                    },
                },
            },
        }).render();
    };

    renderDonut(
        '#attendanceDonutChart',
        chartData.attendance.labels,
        chartData.attendance.series,
        [colors.success, colors.danger]
    );

    renderDonut(
        '#checkoutDonutChart',
        chartData.checkout.labels,
        chartData.checkout.series,
        [colors.info, colors.secondary, colors.warning]
    );

    const punctualityElement = document.querySelector('#punctualityBarChart');
    if (punctualityElement && window.ApexCharts) {
        new ApexCharts(punctualityElement, {
            ...baseChart,
            chart: { ...baseChart.chart, type: 'bar', height: 260 },
            series: [{ name: 'Murid', data: chartData.punctuality.series }],
            colors: [colors.primary],
            plotOptions: {
                bar: {
                    borderRadius: 6,
                    columnWidth: '42%',
                    distributed: true,
                },
            },
            xaxis: {
                categories: chartData.punctuality.labels,
                labels: { style: { colors: [textColor, textColor] } },
            },
            yaxis: {
                min: 0,
                labels: { style: { colors: textColor } },
            },
            grid: { borderColor },
            legend: { show: false },
        }).render();
    }

    const trendElement = document.querySelector('#attendanceTrendChart');
    if (trendElement && window.ApexCharts) {
        new ApexCharts(trendElement, {
            ...baseChart,
            chart: { ...baseChart.chart, type: 'area', height: 320 },
            series: [
                { name: 'Hadir', data: chartData.trend.present },
                { name: 'Belum Hadir', data: chartData.trend.absent },
                { name: 'Terlambat', data: chartData.trend.late },
            ],
            colors: [colors.success, colors.danger, colors.warning],
            stroke: { curve: 'smooth', width: 3 },
            fill: {
                type: 'gradient',
                gradient: {
                    shadeIntensity: 0.4,
                    opacityFrom: 0.35,
                    opacityTo: 0.05,
                    stops: [0, 90, 100],
                },
            },
            xaxis: {
                categories: chartData.trend.labels,
                labels: { style: { colors: textColor } },
            },
            yaxis: {
                min: 0,
                labels: { style: { colors: textColor } },
            },
            grid: { borderColor },
        }).render();
    }
});
</script>
@endsection
