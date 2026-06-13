<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Cetak Nametag Murid</title>
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            background: #f4f5f7;
            color: #1f2937;
            font-family: Arial, Helvetica, sans-serif;
        }

        .toolbar {
            position: sticky;
            top: 0;
            z-index: 10;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            padding: 14px 20px;
            background: #ffffff;
            border-bottom: 1px solid #d9dee3;
        }

        .toolbar h1 {
            margin: 0;
            font-size: 18px;
        }

        .toolbar button {
            border: 0;
            border-radius: 6px;
            padding: 10px 16px;
            color: #ffffff;
            background: #696cff;
            font-weight: 700;
            cursor: pointer;
        }

        .sheet {
            display: grid;
            grid-template-columns: repeat(auto-fill, 54mm);
            justify-content: center;
            gap: 10mm;
            padding: 12mm;
        }

        .nametag {
            width: 54mm;
            height: 86mm;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 5mm 4mm;
            overflow: hidden;
            background: #ffffff;
            border: 1px solid #d9dee3;
            border-radius: 4mm;
            page-break-inside: avoid;
        }

        .school-name {
            min-height: 12mm;
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            font-size: 10px;
            line-height: 1.25;
            font-weight: 800;
            text-transform: uppercase;
        }

        .school-logo {
            width: 12mm;
            height: 12mm;
            margin-bottom: 2mm;
            object-fit: contain;
        }

        .photo {
            width: 28mm;
            height: 34mm;
            margin-top: 3mm;
            border: 1px solid #d9dee3;
            border-radius: 3mm;
            object-fit: cover;
            background: #eef0f4;
        }

        .photo-placeholder {
            width: 28mm;
            height: 34mm;
            margin-top: 3mm;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 1px solid #d9dee3;
            border-radius: 3mm;
            background: #eef0f4;
            color: #697a8d;
            font-size: 28px;
            font-weight: 800;
        }

        .student-name {
            width: 100%;
            margin-top: 4mm;
            text-align: center;
            font-size: 12px;
            line-height: 1.2;
            font-weight: 800;
        }

        .student-nisn {
            margin-top: 1mm;
            color: #566a7f;
            font-size: 9px;
            font-weight: 700;
        }

        .qr {
            width: 21mm;
            height: 21mm;
            margin-top: auto;
        }

        .uuid {
            width: 100%;
            margin-top: 1mm;
            overflow: hidden;
            text-align: center;
            color: #697a8d;
            font-size: 5.5px;
            white-space: nowrap;
        }

        @page {
            size: A4;
            margin: 10mm;
        }

        @media print {
            body {
                background: #ffffff;
            }

            .toolbar {
                display: none;
            }

            .sheet {
                padding: 0;
                gap: 6mm;
            }

            .nametag {
                box-shadow: none;
            }
        }
    </style>
</head>
<body>
    <div class="toolbar">
        <h1>Cetak Nametag Murid - {{ $school->name }}</h1>
        <button type="button" onclick="window.print()">Cetak</button>
    </div>

    <main class="sheet">
        @forelse($students as $student)
            <section class="nametag">
                @if($school->logo_path)
                    <img class="school-logo" src="{{ \App\Support\SchoolFileStorage::url($school->logo_path) }}" alt="Logo {{ $school->name }}">
                @endif
                <div class="school-name">{{ $school->name }}</div>

                @if($student->photo_path)
                    <img class="photo" src="{{ \App\Support\SchoolFileStorage::url($student->photo_path) }}" alt="Foto {{ $student->user?->name }}">
                @else
                    <div class="photo-placeholder">{{ strtoupper(substr($student->user?->name ?? 'M', 0, 1)) }}</div>
                @endif

                <div class="student-name">{{ $student->user?->name }}</div>
                <div class="student-nisn">NISN: {{ $student->nisn ?: '-' }}</div>

                <img class="qr" src="https://api.qrserver.com/v1/create-qr-code/?size=180x180&margin=1&data={{ rawurlencode($student->id) }}" alt="QR {{ $student->id }}">
                <div class="uuid">{{ $student->id }}</div>
            </section>
        @empty
            <p>Tidak ada data murid untuk dicetak.</p>
        @endforelse
    </main>
</body>
</html>
