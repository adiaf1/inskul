<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Cetak Jadwal - {{ $school->name }}</title>
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
            color: #1f2937;
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            background: #f3f4f6;
        }

        .toolbar {
            position: sticky;
            top: 0;
            z-index: 10;
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
            width: 100%;
            padding: 16px;
        }

        .sheet {
            min-height: 180mm;
            margin-bottom: 14px;
            padding: 18px;
            background: #ffffff;
            border: 1px solid #e5e7eb;
        }

        .header {
            padding-bottom: 12px;
            margin-bottom: 12px;
            border-bottom: 2px solid #111827;
        }

        .letterhead {
            display: grid;
            grid-template-columns: 72px 1fr 72px;
            align-items: center;
            gap: 14px;
            text-align: center;
        }

        .letterhead-logo {
            width: 64px;
            height: 64px;
            object-fit: contain;
        }

        .letterhead-name {
            margin: 0;
            color: #111827;
            font-size: 19px;
            font-weight: 800;
            text-transform: uppercase;
        }

        .letterhead-info {
            margin-top: 3px;
            color: #374151;
            font-size: 11px;
        }

        .document-title {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 16px;
            margin-bottom: 12px;
        }

        h1,
        h2 {
            margin: 0;
        }

        h1 {
            font-size: 17px;
            text-transform: uppercase;
            letter-spacing: 0;
        }

        h2 {
            font-size: 15px;
            margin-top: 6px;
        }

        .muted {
            color: #6b7280;
        }

        .filters {
            display: grid;
            grid-template-columns: repeat(2, minmax(120px, 1fr));
            gap: 4px 16px;
            min-width: 260px;
            font-size: 11px;
        }

        .filter-label {
            color: #6b7280;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            padding: 6px;
            border: 1px solid #d1d5db;
            vertical-align: top;
        }

        th {
            color: #111827;
            background: #eef2ff;
            text-align: left;
            font-weight: 700;
        }

        tr:nth-child(even) td {
            background: #f9fafb;
        }

        .day {
            width: 72px;
            font-weight: 700;
        }

        .time {
            width: 82px;
            white-space: nowrap;
        }

        .subject {
            width: 150px;
            font-weight: 700;
        }

        .teacher {
            width: 120px;
        }

        .room {
            width: 95px;
        }

        .empty {
            padding: 28px;
            color: #6b7280;
            text-align: center;
            border: 1px dashed #d1d5db;
            background: #f9fafb;
        }

        .class-sheet {
            page-break-after: always;
        }

        .class-sheet:last-child {
            page-break-after: auto;
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
                margin: 0;
                border: 0;
            }
        }
    </style>
</head>
<body>
    <div class="toolbar">
        <a href="{{ route('schedules.index', request()->query()) }}" class="button">Kembali</a>
        <button type="button" class="button button-primary" onclick="window.print()">Cetak</button>
    </div>

    <main class="page">
        @forelse($groupedSchedules as $classroomSchedules)
            @php
                $firstSchedule = $classroomSchedules->first();
                $classroom = $firstSchedule?->classroom;
            @endphp

            <section class="sheet class-sheet">
                <header class="header">
                    <div class="letterhead">
                        <div>
                            @if($school->logo_path)
                                <img class="letterhead-logo" src="{{ \App\Support\SchoolFileStorage::url($school->logo_path) }}" alt="Logo {{ $school->name }}">
                            @endif
                        </div>
                        <div>
                            <div class="letterhead-name">{{ $school->name }}</div>
                            <div class="letterhead-info">{{ $school->address ?: 'Alamat belum diisi' }}</div>
                            <div class="letterhead-info">
                                Telp: {{ $school->phone ?: '-' }} | Email: {{ $school->email ?: '-' }}
                            </div>
                        </div>
                        <div></div>
                    </div>
                </header>

                <div class="document-title">
                    <div>
                        <h1>Jadwal Pelajaran</h1>
                        <h2>{{ $classroom?->name ?? 'Rombel Tidak Diketahui' }}</h2>
                    </div>
                    <div class="filters">
                        <div>
                            <span class="filter-label">Tahun Ajaran</span><br>
                            <strong>{{ $selectedAcademicYear?->name ?? $firstSchedule?->academicYear?->name ?? 'Semua' }}</strong>
                        </div>
                        <div>
                            <span class="filter-label">Semester</span><br>
                            <strong>{{ $selectedSemester?->name ?? $firstSchedule?->semester?->name ?? 'Semua' }}</strong>
                        </div>
                        <div>
                            <span class="filter-label">Hari</span><br>
                            <strong>{{ $dayOfWeek ? ($days[(int) $dayOfWeek] ?? '-') : 'Semua Hari' }}</strong>
                        </div>
                        <div>
                            <span class="filter-label">Ruangan</span><br>
                            <strong>{{ $selectedRoom?->name ?? 'Semua Ruangan' }}</strong>
                        </div>
                    </div>
                </div>

                <table>
                    <thead>
                        <tr>
                            <th class="day">Hari</th>
                            <th class="time">Jam</th>
                            <th class="subject">Mata Pelajaran</th>
                            <th class="teacher">Guru</th>
                            <th class="room">Ruangan</th>
                            <th>Catatan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($classroomSchedules as $schedule)
                            <tr>
                                <td class="day">{{ $days[$schedule->day_of_week] ?? '-' }}</td>
                                <td class="time">{{ substr($schedule->starts_at, 0, 5) }} - {{ substr($schedule->ends_at, 0, 5) }}</td>
                                <td>
                                    <div class="subject">{{ $schedule->subject?->name ?? '-' }}</div>
                                    @if($schedule->subject?->code)
                                        <div class="muted">{{ $schedule->subject->code }}</div>
                                    @endif
                                </td>
                                <td>{{ $schedule->teacher?->user?->name ?? '-' }}</td>
                                <td>
                                    {{ $schedule->physicalRoom?->name ?: ($schedule->room ?: '-') }}
                                    @if($schedule->physicalRoom?->code)
                                        <div class="muted">{{ $schedule->physicalRoom->code }}</div>
                                    @endif
                                </td>
                                <td>{{ $schedule->notes ?: '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </section>
        @empty
            <section class="sheet">
                <header class="header">
                    <div class="letterhead">
                        <div>
                            @if($school->logo_path)
                                <img class="letterhead-logo" src="{{ \App\Support\SchoolFileStorage::url($school->logo_path) }}" alt="Logo {{ $school->name }}">
                            @endif
                        </div>
                        <div>
                            <div class="letterhead-name">{{ $school->name }}</div>
                            <div class="letterhead-info">{{ $school->address ?: 'Alamat belum diisi' }}</div>
                            <div class="letterhead-info">
                                Telp: {{ $school->phone ?: '-' }} | Email: {{ $school->email ?: '-' }}
                            </div>
                        </div>
                        <div></div>
                    </div>
                </header>

                <div class="document-title">
                    <div>
                        <h1>Jadwal Pelajaran</h1>
                        <h2>{{ $selectedClassroom?->name ?? 'Semua Rombel' }}</h2>
                    </div>
                </div>

                <div class="empty">Tidak ada jadwal sesuai filter yang dipilih.</div>
            </section>
        @endforelse
    </main>
</body>
</html>
