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
            position: relative;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 3mm 4mm;
            overflow: hidden;
            background: #ffffff;
            border: 1px solid #d9dee3;
            border-radius: 4mm;
            page-break-inside: avoid;
            print-color-adjust: exact;
            -webkit-print-color-adjust: exact;
        }

        .nametag-background {
            position: absolute;
            inset: 0;
            z-index: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .nametag-content {
            position: relative;
            z-index: 1;
            width: 100%;
            height: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .school-name {
            height: 8mm;
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            overflow: hidden;
            font-size: 9px;
            line-height: 1.25;
            font-weight: 800;
            text-transform: uppercase;
        }

        .school-logo {
            flex: 0 0 auto;
            width: 8mm;
            height: 8mm;
            margin-bottom: .75mm;
            object-fit: contain;
        }

        .photo-frame {
            flex: 0 0 29mm;
            width: 25mm;
            height: 29mm;
            margin-top: 2mm;
            border: 1px solid #d9dee3;
            border-radius: 2.5mm;
            overflow: hidden;
            background: #eef0f4;
        }

        .photo {
            display: block;
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .photo-placeholder {
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #697a8d;
            font-size: 24px;
            font-weight: 800;
        }

        .student-name {
            flex: 0 0 9mm;
            width: 100%;
            margin-top: 2mm;
            text-align: center;
            overflow: hidden;
            font-size: 10px;
            line-height: 1.2;
            font-weight: 800;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            word-break: break-word;
        }

        .student-nisn {
            flex: 0 0 3.5mm;
            margin-top: .5mm;
            color: #566a7f;
            font-size: 9px;
            font-weight: 700;
            line-height: 1.1;
        }

        .qr {
            flex: 0 0 18mm;
            width: 18mm;
            height: 18mm;
            margin-top: auto;
            object-fit: contain;
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
            <section
                class="nametag"
            >
                @if($school->nametag_background_path)
                    <img class="nametag-background" src="{{ \App\Support\SchoolFileStorage::url($school->nametag_background_path) }}" alt="">
                @endif
                <div class="nametag-content">
                    @if($school->logo_path)
                        <img class="school-logo" src="{{ \App\Support\SchoolFileStorage::url($school->logo_path) }}" alt="Logo {{ $school->name }}">
                    @endif
                    <div class="school-name">{{ $school->name }}</div>

                    <div class="photo-frame">
                        @if($student->photo_path)
                            <img class="photo" src="{{ \App\Support\SchoolFileStorage::url($student->photo_path) }}" alt="Foto {{ $student->user?->name }}">
                        @else
                            <div class="photo-placeholder">{{ strtoupper(substr($student->user?->name ?? 'M', 0, 1)) }}</div>
                        @endif
                    </div>

                    <div class="student-name">{{ $student->user?->name }}</div>
                    <div class="student-nisn">NISN: {{ $student->nisn ?: '-' }}</div>

                    <img class="qr" src="https://api.qrserver.com/v1/create-qr-code/?size=180x180&margin=1&data={{ rawurlencode($student->id) }}" alt="QR {{ $student->id }}">
                </div>
            </section>
        @empty
            <p>Tidak ada data murid untuk dicetak.</p>
        @endforelse
    </main>
</body>
</html>
