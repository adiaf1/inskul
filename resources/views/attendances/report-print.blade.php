<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Print Report Presensi - {{ $school->name }}</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 12mm;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            color: #111827;
            font-family: Arial, sans-serif;
            font-size: 11px;
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

        .letterhead-info,
        .muted {
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
        }

        th,
        td {
            padding: 5px;
            border: 1px solid #d1d5db;
            vertical-align: top;
        }

        th {
            background: #eef2ff;
            text-align: left;
        }

        .status {
            white-space: nowrap;
            font-weight: 700;
        }

        .empty {
            padding: 24px;
            text-align: center;
            color: #6b7280;
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
        $typeLabels = ['' => 'Semua Jenis', 'daily' => 'Harian', 'schedule' => 'Per Jadwal'];
        $statusLabels = ['' => 'Semua Status', 'unfilled' => 'Belum Diisi'] + $recordStatuses;
    @endphp

    <div class="toolbar">
        <a href="{{ route('attendances.report', request()->query()) }}" class="button">Kembali</a>
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
                <h1>Report Presensi</h1>
                <div class="muted">{{ $filters['date_from'] }} sampai {{ $filters['date_to'] }}</div>
            </div>

            <div class="filters">
                <div>Jenis: <strong>{{ $typeLabels[$filters['type']] ?? '-' }}</strong></div>
                <div>Rombel: <strong>{{ $selectedClassroom?->name ?? 'Semua Rombel' }}</strong></div>
                <div>Status: <strong>{{ $statusLabels[$filters['status']] ?? '-' }}</strong></div>
                <div>Total Data: <strong>{{ $records->count() }}</strong></div>
            </div>

            <table>
                <thead>
                    <tr>
                        <th style="width: 26px;">No</th>
                        <th>Tanggal</th>
                        <th>Jenis</th>
                        <th>Rombel</th>
                        <th>Jadwal/Mapel</th>
                        <th>Nama</th>
                        <th>Status</th>
                        <th>Catatan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($records as $record)
                        @php
                            $session = $record->session;
                            $status = $record->status;
                        @endphp
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $session?->attendance_date?->format('d M Y') ?? '-' }}</td>
                            <td>{{ $typeLabels[$session?->type ?? ''] ?? '-' }}</td>
                            <td>{{ $session?->classroom?->name ?? '-' }}</td>
                            <td>
                                @if($session?->type === 'schedule')
                                    {{ $session->subject?->name ?? $session->schedule?->subject?->name ?? '-' }}
                                    <div class="muted">{{ substr($session->starts_at, 0, 5) }} - {{ substr($session->ends_at, 0, 5) }}</div>
                                @else
                                    Presensi Per Kelas
                                @endif
                            </td>
                            <td>
                                {{ $record->student?->user?->name ?? '-' }}
                                <div class="muted">{{ $record->student?->nis ?: '-' }}{{ $record->student?->nisn ? ' / '.$record->student?->nisn : '' }}</div>
                            </td>
                            <td class="status">
                                {{ $status ? ($recordStatuses[$status] ?? $status) : 'Belum Diisi' }}
                                @if($record->checked_at)
                                    <div class="muted">{{ $record->checked_at->format('H:i') }}</div>
                                @endif
                            </td>
                            <td>{{ $record->notes ?: '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="empty">Tidak ada data presensi sesuai filter.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </section>
    </main>
</body>
</html>
