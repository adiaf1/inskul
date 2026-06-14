<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Print Report Presensi Per Jadwal - {{ $school->name }}</title>
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

        .sheet + .sheet {
            margin-top: 16px;
        }

        .print-sheet {
            page-break-after: always;
        }

        .print-sheet:last-child {
            page-break-after: auto;
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
            padding: 4px;
            border: 1px solid #d1d5db;
            vertical-align: top;
        }

        th {
            background: #eef2ff;
            text-align: left;
        }

        .status,
        .date-column {
            width: 28px;
            min-width: 28px;
            text-align: center;
            white-space: nowrap;
            font-weight: 700;
        }

        .student-column {
            min-width: 150px;
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
        $shortStatusLabels = [
            'present' => 'H',
            'sick' => 'S',
            'absent' => 'A',
            'permit' => 'I',
            'late' => 'T',
        ];
    @endphp

    <div class="toolbar">
        <a href="{{ route('attendances.report.schedule', request()->query()) }}" class="button">Kembali</a>
        <button type="button" class="button button-primary" onclick="window.print()">Print</button>
    </div>

    <main class="page">
        @forelse($subjectSheets as $sheet)
            <section class="sheet print-sheet">
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
                    <h1>Report Presensi Per Jadwal</h1>
                    <div class="muted">{{ $filters['date_from'] }} sampai {{ $filters['date_to'] }}</div>
                </div>

                <div class="filters">
                    <div>Rombel: <strong>{{ $selectedClassroom?->name ?? $sheet['classroom']?->name ?? 'Semua Rombel' }}</strong></div>
                    <div>Mata Pelajaran: <strong>{{ $sheet['subject']?->name ?? 'Mata Pelajaran Tidak Diketahui' }}</strong></div>
                    <div>Guru: <strong>{{ $selectedTeacher?->user?->name ?? $sheet['teacher']?->user?->name ?? 'Semua Guru' }}</strong></div>
                    <div>Semester: <strong>{{ $selectedClassroom?->semester?->name ?? $sheet['classroom']?->semester?->name ?? 'Semua Semester' }}</strong></div>
                    <div>Tahun Ajaran: <strong>{{ $selectedClassroom?->academicYear?->name ?? $sheet['classroom']?->academicYear?->name ?? 'Semua Tahun Ajaran' }}</strong></div>
                    <div>Dicetak: <strong>{{ now()->format('d M Y H:i') }}</strong></div>
                </div>

                <table>
                    <thead>
                        <tr>
                            <th style="width: 26px;">No</th>
                            <th class="student-column">Murid</th>
                            @foreach($dateColumns as $date)
                                <th class="date-column">{{ $date->format('d/m') }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($sheet['student_rows'] as $row)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td class="student-column">
                                    {{ $row['student']?->user?->name ?? '-' }}
                                    <div class="muted">{{ $row['student']?->nis ?: '-' }}{{ $row['student']?->nisn ? ' / '.$row['student']?->nisn : '' }}</div>
                                </td>
                                @foreach($dateColumns as $date)
                                    @php
                                        $dateRecords = $row['records_by_date']->get($date->format('Y-m-d'), collect());
                                        $statuses = collect($dateRecords)
                                            ->map(fn ($record) => $record->status ? ($shortStatusLabels[$record->status] ?? '-') : '-')
                                            ->unique()
                                            ->implode('/');
                                    @endphp
                                    <td class="status">{{ $statuses ?: '-' }}</td>
                                @endforeach
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ 2 + $dateColumns->count() }}" class="empty">Tidak ada data presensi per jadwal sesuai filter.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                <div class="muted" style="margin-top: 8px;">
                    Keterangan: H = Hadir, S = Sakit, A = Alpa, I = Izin, T = Terlambat, - = Belum ada data/belum diisi.
                </div>
            </section>
        @empty
            <section class="sheet">
                <div class="empty">Tidak ada data presensi per jadwal sesuai filter.</div>
            </section>
        @endforelse
    </main>
</body>
</html>
