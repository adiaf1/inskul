@extends('layouts.app')

@section('content')
@php
    $selectedDateLabel = \Illuminate\Support\Carbon::parse($filters['date'])->format('d M Y');
    $canOpenAttendanceHub = in_array(\App\Support\EffectiveAccess::role(request()), ['school_admin', 'teacher'], true);
    $manualDailyStatusMeta = [
        'sick' => ['label' => 'Sakit', 'class' => 'bg-label-info'],
        'permit' => ['label' => 'Izin', 'class' => 'bg-label-primary'],
        'absent' => ['label' => 'Alpa', 'class' => 'bg-label-danger'],
    ];
@endphp

<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 py-4 mb-2">
        <div>
            <h4 class="mb-1">Grafik Presensi Harian</h4>
            <p class="text-muted mb-0">{{ $school->name }} - ringkasan scan, sakit, izin, alpa, dan pulang murid.</p>
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
                    {{ $attendanceDayContext['message'] }} Hari sekolah aktif: {{ implode(', ', $schoolAttendanceDayLabels) }}.
                    @unless($isAttendanceDay)
                        Murid yang belum scan pada tanggal ini tidak dihitung sebagai belum hadir.
                    @endunless
                </span>
                @if($attendanceDayContext['semester'])
                    <span class="d-block small">Semester: {{ $attendanceDayContext['semester']->name }}</span>
                @endif
                @if($attendanceDayContext['events']->isNotEmpty())
                    <div class="d-flex flex-wrap gap-2 mt-2">
                        @foreach($attendanceDayContext['events'] as $event)
                            <span class="badge bg-label-secondary">{{ $event->title }}</span>
                        @endforeach
                    </div>
                @endif
            </div>
            <span class="badge {{ $isAttendanceDay ? 'bg-label-primary' : 'bg-label-warning' }}">
                {{ $selectedDateLabel }}
            </span>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-6 col-md-3 col-xl-2">
            <div class="card h-100">
                <div class="card-body">
                    <span class="avatar rounded bg-label-secondary mb-3"><i class="bx bx-group"></i></span>
                    <div class="text-muted small">Murid Aktif</div>
                    <h4 class="mb-0">{{ $summary['total_students'] }}</h4>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3 col-xl-2">
            <div class="card h-100">
                <div class="card-body">
                    <span class="avatar rounded bg-label-success mb-3"><i class="bx bx-user-check"></i></span>
                    <div class="text-muted small">Hadir</div>
                    <h4 class="mb-0">{{ $summary['present'] }}</h4>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3 col-xl-2">
            <div class="card h-100">
                <div class="card-body">
                    <span class="avatar rounded bg-label-info mb-3"><i class="bx bx-plus-medical"></i></span>
                    <div class="text-muted small">Sakit</div>
                    <h4 class="mb-0">{{ $summary['sick'] }}</h4>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3 col-xl-2">
            <div class="card h-100">
                <div class="card-body">
                    <span class="avatar rounded bg-label-primary mb-3"><i class="bx bx-file"></i></span>
                    <div class="text-muted small">Izin</div>
                    <h4 class="mb-0">{{ $summary['permit'] }}</h4>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3 col-xl-2">
            <div class="card h-100">
                <div class="card-body">
                    <span class="avatar rounded bg-label-danger mb-3"><i class="bx bx-user-x"></i></span>
                    <div class="text-muted small">Alpa</div>
                    <h4 class="mb-0">{{ $summary['absent'] }}</h4>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3 col-xl-2">
            <div class="card h-100">
                <div class="card-body">
                    <span class="avatar rounded bg-label-secondary mb-3"><i class="bx bx-help-circle"></i></span>
                    <div class="text-muted small">Belum Diproses</div>
                    <h4 class="mb-0">{{ $summary['unprocessed'] }}</h4>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3 col-xl-2">
            <div class="card h-100">
                <div class="card-body">
                    <span class="avatar rounded bg-label-warning mb-3"><i class="bx bx-time"></i></span>
                    <div class="text-muted small">Terlambat</div>
                    <h4 class="mb-0">{{ $summary['late'] }}</h4>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3 col-xl-2">
            <div class="card h-100">
                <div class="card-body">
                    <span class="avatar rounded bg-label-info mb-3"><i class="bx bx-log-out-circle"></i></span>
                    <div class="text-muted small">Sudah Pulang</div>
                    <h4 class="mb-0">{{ $summary['checked_out'] }}</h4>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3 col-xl-2">
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
                    <h5 class="mb-0">Komposisi Presensi</h5>
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
                    <div class="text-muted small">Hadir, sakit, izin, alpa, belum diproses, dan terlambat.</div>
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
                        <th>Keterangan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($students as $student)
                        @php
                            $attendance = $studentAttendances->get($student->id);
                            $classroomName = $student->classrooms->first()?->name ?? '-';
                            $manualStatus = $attendance && ! $attendance->check_in_at ? ($manualDailyStatusMeta[$attendance->status] ?? null) : null;
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
                                @elseif($manualStatus)
                                    <span class="badge {{ $manualStatus['class'] }}">{{ $manualStatus['label'] }}</span>
                                @else
                                    <span class="badge bg-label-secondary">Belum Diproses</span>
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
                            <td class="text-wrap" style="min-width: 180px;">{{ $attendance?->notes ?: '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">Belum ada murid aktif untuk filter ini.</td>
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
    const charts = [];

    const themePalette = () => {
        const css = getComputedStyle(document.documentElement);
        const isDark = document.documentElement.getAttribute('data-bs-theme') === 'dark';

        return {
            isDark,
            textColor: isDark ? '#cfd3ec' : (css.getPropertyValue('--bs-body-color').trim() || '#566a7f'),
            mutedColor: isDark ? '#a3a7c7' : (css.getPropertyValue('--bs-secondary-color').trim() || '#a1acb8'),
            borderColor: isDark ? '#44485e' : (css.getPropertyValue('--bs-border-color').trim() || '#e6e6e8'),
            cardColor: isDark ? '#2b2c40' : (css.getPropertyValue('--bs-card-bg').trim() || css.getPropertyValue('--bs-paper-bg').trim() || '#fff'),
            colors: {
                success: css.getPropertyValue('--bs-success').trim() || '#71dd37',
                danger: css.getPropertyValue('--bs-danger').trim() || '#ff3e1d',
                warning: css.getPropertyValue('--bs-warning').trim() || '#ffab00',
                info: css.getPropertyValue('--bs-info').trim() || '#03c3ec',
                primary: css.getPropertyValue('--bs-primary').trim() || '#696cff',
                secondary: css.getPropertyValue('--bs-secondary').trim() || '#8592a3',
            },
        };
    };

    const baseChart = (palette) => ({
        chart: {
            toolbar: { show: false },
            foreColor: palette.textColor,
            background: 'transparent',
        },
        dataLabels: { enabled: true },
        legend: {
            labels: { colors: palette.textColor },
        },
        tooltip: {
            theme: palette.isDark ? 'dark' : 'light',
        },
    });

    const renderDonut = (selector, labels, series, chartColors) => {
        const element = document.querySelector(selector);

        if (!element || !window.ApexCharts) {
            return;
        }

        const palette = themePalette();
        const chart = new ApexCharts(element, {
            ...baseChart(palette),
            chart: { ...baseChart(palette).chart, type: 'donut', height: 260 },
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
                                color: palette.textColor,
                            },
                            name: { color: palette.mutedColor },
                            value: { color: palette.textColor },
                        },
                    },
                },
            },
        });

        chart.render();
        charts.push(chart);
    };

    const palette = themePalette();
    const colors = palette.colors;

    renderDonut(
        '#attendanceDonutChart',
        chartData.attendance.labels,
        chartData.attendance.series,
        [colors.success, colors.info, colors.primary, colors.danger, colors.secondary]
    );

    renderDonut(
        '#checkoutDonutChart',
        chartData.checkout.labels,
        chartData.checkout.series,
        [colors.info, colors.secondary, colors.warning]
    );

    const punctualityElement = document.querySelector('#punctualityBarChart');
    if (punctualityElement && window.ApexCharts) {
        const chart = new ApexCharts(punctualityElement, {
            ...baseChart(palette),
            chart: { ...baseChart(palette).chart, type: 'bar', height: 260 },
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
                labels: { style: { colors: [palette.textColor, palette.textColor] } },
                axisBorder: { color: palette.borderColor },
                axisTicks: { color: palette.borderColor },
            },
            yaxis: {
                min: 0,
                labels: { style: { colors: palette.textColor } },
            },
            grid: { borderColor: palette.borderColor },
            legend: { show: false },
        });

        chart.render();
        charts.push(chart);
    }

    const trendElement = document.querySelector('#attendanceTrendChart');
    if (trendElement && window.ApexCharts) {
        const chart = new ApexCharts(trendElement, {
            ...baseChart(palette),
            chart: { ...baseChart(palette).chart, type: 'area', height: 320 },
            series: [
                { name: 'Hadir', data: chartData.trend.present },
                { name: 'Sakit', data: chartData.trend.sick },
                { name: 'Izin', data: chartData.trend.permit },
                { name: 'Alpa', data: chartData.trend.absent },
                { name: 'Belum Diproses', data: chartData.trend.unprocessed },
                { name: 'Terlambat', data: chartData.trend.late },
            ],
            colors: [colors.success, colors.info, colors.primary, colors.danger, colors.secondary, colors.warning],
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
                labels: { style: { colors: palette.textColor } },
                axisBorder: { color: palette.borderColor },
                axisTicks: { color: palette.borderColor },
            },
            yaxis: {
                min: 0,
                labels: { style: { colors: palette.textColor } },
            },
            grid: { borderColor: palette.borderColor },
        });

        chart.render();
        charts.push(chart);
    }

    const updateChartTheme = () => {
        const nextPalette = themePalette();

        charts.forEach((chart) => {
            chart.updateOptions({
                chart: {
                    foreColor: nextPalette.textColor,
                    background: 'transparent',
                },
                legend: {
                    labels: { colors: nextPalette.textColor },
                },
                tooltip: {
                    theme: nextPalette.isDark ? 'dark' : 'light',
                },
                plotOptions: {
                    pie: {
                        donut: {
                            labels: {
                                total: { color: nextPalette.textColor },
                                name: { color: nextPalette.mutedColor },
                                value: { color: nextPalette.textColor },
                            },
                        },
                    },
                },
                xaxis: {
                    labels: { style: { colors: nextPalette.textColor } },
                    axisBorder: { color: nextPalette.borderColor },
                    axisTicks: { color: nextPalette.borderColor },
                },
                yaxis: {
                    labels: { style: { colors: nextPalette.textColor } },
                },
                grid: { borderColor: nextPalette.borderColor },
            }, false, true);
        });
    };

    new MutationObserver((mutations) => {
        if (mutations.some((mutation) => mutation.attributeName === 'data-bs-theme')) {
            updateChartTheme();
        }
    }).observe(document.documentElement, { attributes: true });
});
</script>
@endsection
