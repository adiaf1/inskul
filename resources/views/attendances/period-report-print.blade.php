<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Rekap Presensi Periode - {{ $school->name }}</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 12mm;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            color: #111827;
            font-family: Arial, sans-serif;
            font-size: 10px;
            line-height: 1.35;
            background: #f3f4f6;
        }

        .toolbar {
            position: sticky;
            top: 0;
            display: flex;
            justify-content: flex-end;
            gap: 8px;
            padding: 12px;
            background: #ffffff;
            border-bottom: 1px solid #d1d5db;
        }

        .button {
            border: 1px solid #d1d5db;
            border-radius: 6px;
            padding: 8px 12px;
            color: #111827;
            background: #ffffff;
            text-decoration: none;
            cursor: pointer;
            font-size: 12px;
        }

        .button-primary {
            border-color: #696cff;
            color: #ffffff;
            background: #696cff;
        }

        .page {
            padding: 16px;
        }

        .sheet {
            padding: 18px;
            background: #ffffff;
            border: 1px solid #e5e7eb;
        }

        .letterhead {
            display: grid;
            grid-template-columns: 64px 1fr 64px;
            align-items: center;
            gap: 12px;
            padding-bottom: 10px;
            border-bottom: 2px solid #111827;
            text-align: center;
        }

        .letterhead-logo {
            width: 56px;
            height: 56px;
            object-fit: contain;
        }

        .letterhead-name {
            margin: 0;
            font-size: 17px;
            font-weight: 800;
            text-transform: uppercase;
        }

        .muted,
        .letterhead-info {
            color: #6b7280;
        }

        .title {
            margin: 12px 0;
            text-align: center;
        }

        .title h1 {
            margin: 0;
            font-size: 15px;
            text-transform: uppercase;
        }

        .filters {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 4px 18px;
            margin-bottom: 12px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 14px;
        }

        th,
        td {
            padding: 4px;
            border: 1px solid #d1d5db;
            vertical-align: top;
        }

        th {
            background: #eef2ff;
            text-align: left;
        }

        .text-center {
            text-align: center;
        }

        .section-title {
            margin: 14px 0 6px;
            font-size: 12px;
        }

        .empty {
            padding: 24px;
            text-align: center;
            color: #6b7280;
        }

        .signature-row {
            display: flex;
            justify-content: flex-end;
            margin-top: 22px;
            break-inside: avoid;
        }

        .signature-box {
            width: 240px;
            text-align: center;
        }

        .signature-space {
            height: 64px;
        }

        .signature-name {
            font-weight: 700;
            text-decoration: underline;
        }

        @media print {
            body {
                background: #ffffff;
            }

            .toolbar {
                display: none;
            }

            .page,
            .sheet {
                padding: 0;
                border: 0;
            }
        }
    </style>
</head>
<body>
    @php
        $periodLabel = \Illuminate\Support\Carbon::parse($filters['date_from'])->format('d M Y').' - '.\Illuminate\Support\Carbon::parse($filters['date_to'])->format('d M Y');
    @endphp

    <div class="toolbar">
        <a href="{{ route('attendances.report.period', request()->query()) }}" class="button">Kembali</a>
        <button type="button" class="button button-primary" onclick="window.print()">Print</button>
    </div>

    <main class="page">
        <section class="sheet">
            <header class="letterhead">
                <div>
                    @if($school->logo_path)
                        <img class="letterhead-logo" src="{{ \App\Support\SchoolFileStorage::url($school->logo_path) }}" alt="Logo {{ $school->name }}">
                    @endif
                </div>
                <div>
                    <div class="letterhead-name">{{ $school->name }}</div>
                    <div class="letterhead-info">{{ $school->address ?: 'Alamat belum diisi' }}</div>
                    <div class="letterhead-info">Telp: {{ $school->phone ?: '-' }} | Email: {{ $school->email ?: '-' }}</div>
                </div>
                <div></div>
            </header>

            <div class="title">
                <h1>Rekap Presensi Periode</h1>
                <div class="muted">{{ $periodLabel }}</div>
            </div>

            <div class="filters">
                <div>Rombel: <strong>{{ $selectedClassroom?->name ?? 'Semua Rombel' }}</strong></div>
                <div>Hari Efektif: <strong>{{ $effectiveDates->count() }} hari</strong></div>
                <div>Wali Kelas: <strong>{{ $selectedClassroom?->homeroomTeacher?->user?->name ?? 'Semua Wali Kelas' }}</strong></div>
                <div>Dicetak: <strong>{{ now()->format('d M Y H:i') }}</strong></div>
            </div>

            <h2 class="section-title">Rekap Per Rombel</h2>
            <table>
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
                            <td>{{ $row['classroom']->name }}</td>
                            <td>{{ $row['classroom']->homeroomTeacher?->user?->name ?? '-' }}</td>
                            <td class="text-center">{{ $row['student_count'] }}</td>
                            <td class="text-center">{{ $row['target'] }}</td>
                            <td class="text-center">{{ $row['present'] }} ({{ $row['present_percent'] }}%)</td>
                            <td class="text-center">{{ $row['sick'] }} ({{ $row['sick_percent'] }}%)</td>
                            <td class="text-center">{{ $row['permit'] }} ({{ $row['permit_percent'] }}%)</td>
                            <td class="text-center">{{ $row['absent'] }} ({{ $row['absent_percent'] }}%)</td>
                            <td class="text-center">{{ $row['explained'] }} ({{ $row['explained_percent'] }}%)</td>
                            <td class="text-center">{{ $row['unprocessed'] }} ({{ $row['unprocessed_percent'] }}%)</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="empty">Tidak ada rombel aktif sesuai filter.</td>
                        </tr>
                    @endforelse
                    <tr>
                        <th colspan="2">Total</th>
                        <th class="text-center">{{ $totals['student_count'] }}</th>
                        <th class="text-center">{{ $totals['target'] }}</th>
                        <th class="text-center">{{ $totals['present'] }} ({{ $totals['present_percent'] }}%)</th>
                        <th class="text-center">{{ $totals['sick'] }} ({{ $totals['sick_percent'] }}%)</th>
                        <th class="text-center">{{ $totals['permit'] }} ({{ $totals['permit_percent'] }}%)</th>
                        <th class="text-center">{{ $totals['absent'] }} ({{ $totals['absent_percent'] }}%)</th>
                        <th class="text-center">{{ $totals['explained'] }} ({{ $totals['explained_percent'] }}%)</th>
                        <th class="text-center">{{ $totals['unprocessed'] }} ({{ $totals['unprocessed_percent'] }}%)</th>
                    </tr>
                </tbody>
            </table>

            @foreach($summaryRows as $row)
                <h2 class="section-title">Detail {{ $row['classroom']->name }} - {{ $row['classroom']->homeroomTeacher?->user?->name ?? 'Tanpa wali kelas' }}</h2>
                <table>
                    <thead>
                        <tr>
                            <th style="width: 28px;">No</th>
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
                                    {{ $studentRow['student']?->user?->name ?? '-' }}
                                    <div class="muted">{{ $studentRow['student']?->nis ?: '-' }}{{ $studentRow['student']?->nisn ? ' / '.$studentRow['student']?->nisn : '' }}</div>
                                </td>
                                <td class="text-center">{{ $studentRow['present'] }} ({{ $studentRow['present_percent'] }}%)</td>
                                <td class="text-center">{{ $studentRow['sick'] }} ({{ $studentRow['sick_percent'] }}%)</td>
                                <td class="text-center">{{ $studentRow['permit'] }} ({{ $studentRow['permit_percent'] }}%)</td>
                                <td class="text-center">{{ $studentRow['absent'] }} ({{ $studentRow['absent_percent'] }}%)</td>
                                <td class="text-center">{{ $studentRow['explained'] }} ({{ $studentRow['explained_percent'] }}%)</td>
                                <td class="text-center">{{ $studentRow['unprocessed'] }} ({{ $studentRow['unprocessed_percent'] }}%)</td>
                                <td class="text-center">{{ $studentRow['late'] }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="empty">Belum ada murid aktif pada rombel ini.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            @endforeach

            <div class="signature-row">
                <div class="signature-box">
                    <div>{{ now()->format('d M Y') }}</div>
                    <div>Kepala Sekolah</div>
                    <div class="signature-space"></div>
                    <div class="signature-name">{{ $school->principal_name ?: '................................' }}</div>
                    <div>NIP. {{ $school->principal_nip ?: '................................' }}</div>
                </div>
            </div>
        </section>
    </main>
</body>
</html>
