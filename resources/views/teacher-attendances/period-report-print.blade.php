<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Rekap Presensi Guru - {{ $school->name }}</title>
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
            padding: 5px 6px;
            border: 1px solid #d1d5db;
            vertical-align: top;
            text-align: center;
        }

        th {
            background: #eef2ff;
            text-align: center;
        }

        .left {
            text-align: left;
        }

        .signature-row {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            gap: 18px;
            margin-top: 28px;
            break-inside: avoid;
        }

        .signature-box {
            width: 45%;
            max-width: 280px;
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
        $periodLabel = \Illuminate\Support\Carbon::parse($dateFrom)->format('d M Y').' - '.\Illuminate\Support\Carbon::parse($dateTo)->format('d M Y');
    @endphp

    <div class="toolbar">
        <a href="{{ route('teacher-attendances.report.period', request()->query()) }}" class="button">Kembali</a>
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
                <h1>Rekap Presensi Guru</h1>
                <div class="muted">{{ $periodLabel }}</div>
            </div>

            <div class="filters">
                <div>Dicetak: <strong>{{ now()->format('d M Y H:i') }}</strong></div>
                <div>Jumlah Guru: <strong>{{ $totals['teacher_count'] }}</strong></div>
            </div>

            <table>
                <thead>
                    <tr>
                        <th class="left">Nama</th>
                        <th class="left">NIP/NUPTK</th>
                        <th>Hari Efektif</th>
                        <th>Hadir</th>
                        <th>Terlambat</th>
                        <th>Pulang</th>
                        <th>Pulang Cepat</th>
                        <th>Luar Area</th>
                        <th>Belum Absen</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($summaryRows as $row)
                        <tr>
                            <td class="left">{{ $row['teacher']->user?->name ?? '-' }}</td>
                            <td class="left">{{ $row['teacher']->nip ?? $row['teacher']->nuptk ?? '-' }}</td>
                            <td>{{ $row['expected_days'] }}</td>
                            <td>{{ $row['hadir'] }}</td>
                            <td>{{ $row['terlambat'] }}</td>
                            <td>{{ $row['pulang'] }}</td>
                            <td>{{ $row['pulang_cepat'] }}</td>
                            <td>{{ $row['di_luar_area'] }}</td>
                            <td>{{ $row['belum_absen'] }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9">Tidak ada data presensi guru sesuai rentang tanggal.</td>
                        </tr>
                    @endforelse
                    <tr>
                        <th class="left">Total</th>
                        <th class="left">-</th>
                        <th>{{ $totals['teacher_count'] }}</th>
                        <th>{{ $totals['hadir'] }}</th>
                        <th>{{ $totals['terlambat'] }}</th>
                        <th>{{ $totals['pulang'] }}</th>
                        <th>{{ $totals['pulang_cepat'] }}</th>
                        <th>{{ $totals['di_luar_area'] }}</th>
                        <th>{{ $totals['belum_absen'] }}</th>
                    </tr>
                </tbody>
            </table>

            <div class="signature-row">
                <div class="signature-box" style="margin-left: auto;">
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
