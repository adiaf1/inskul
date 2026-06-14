@extends('layouts.app')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    @if(session('success') || $errors->any())
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                Swal.fire({
                    title: '{{ session('success') ? 'Sukses!' : 'Error!' }}',
                    text: '{{ session('success') ?: $errors->first() }}',
                    icon: '{{ session('success') ? 'success' : 'error' }}',
                    confirmButtonText: 'OK'
                });
            });
        </script>
    @endif

    <style>
        .attendance-status-group {
            display: inline-flex;
            flex-wrap: wrap;
            gap: 4px;
        }

        .attendance-status-button {
            width: 34px;
            height: 34px;
            padding: 0;
            font-weight: 700;
        }
    </style>

    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 py-4 mb-2">
        <div>
            <h4 class="mb-1">Input Presensi Per Jadwal</h4>
            <p class="text-muted mb-0">
                {{ $session->classroom?->name }} - {{ $session->subject?->name ?? '-' }} - {{ $session->attendance_date->format('d M Y') }}
            </p>
        </div>

        <a href="{{ route('attendances.schedule') }}" class="btn btn-label-secondary">Kembali</a>
    </div>

    <form method="POST" action="{{ route('attendances.schedule.update', $session) }}">
        @csrf
        @method('PUT')

        <div class="card mb-4">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-3">
                        <div class="text-muted small">Tanggal</div>
                        <strong>{{ $session->attendance_date->format('d M Y') }}</strong>
                    </div>
                    <div class="col-md-3">
                        <div class="text-muted small">Hari/Jam</div>
                        <strong>{{ $session->schedule ? ($days[$session->schedule->day_of_week] ?? '-') : '-' }}</strong>
                        <div class="text-muted small">{{ substr($session->starts_at, 0, 5) }} - {{ substr($session->ends_at, 0, 5) }}</div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-muted small">Rombel</div>
                        <strong>{{ $session->classroom?->name ?? '-' }}</strong>
                    </div>
                    <div class="col-md-3">
                        <div class="text-muted small">Status</div>
                        <span @class([
                            'badge',
                            'bg-label-secondary' => $session->status === 'draft',
                            'bg-label-success' => $session->status === 'submitted',
                            'bg-label-dark' => $session->status === 'locked',
                        ])>
                            {{ ['draft' => 'Draft', 'submitted' => 'Submitted', 'locked' => 'Dikunci'][$session->status] ?? ucfirst($session->status) }}
                        </span>
                    </div>
                    <div class="col-md-3">
                        <div class="text-muted small">Mata Pelajaran</div>
                        <strong>{{ $session->subject?->name ?? '-' }}</strong>
                    </div>
                    <div class="col-md-3">
                        <div class="text-muted small">Guru</div>
                        <strong>{{ $session->teacher?->user?->name ?? '-' }}</strong>
                    </div>
                    <div class="col-md-3">
                        <div class="text-muted small">Ruangan</div>
                        <strong>{{ $session->schedule?->physicalRoom?->name ?: ($session->schedule?->room ?: '-') }}</strong>
                    </div>
                </div>

                <div class="mt-4">
                    <label class="form-label" for="notes">Catatan Sesi</label>
                    <textarea class="form-control" id="notes" name="notes" rows="2">{{ old('notes', $session->notes) }}</textarea>
                </div>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-body">
                <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-3">
                    <div>
                        <h6 class="mb-1">Scan QR Nametag</h6>
                        <div class="text-muted small">Arahkan kamera ke QR Code pada nametag murid untuk menandai hadir pada jadwal ini.</div>
                    </div>
                    <div class="d-flex flex-wrap gap-2">
                        <button type="button" class="btn btn-label-primary" id="startQrScanner" @disabled($session->status === 'locked')>
                            <i class="bx bx-camera me-1"></i> Buka Kamera
                        </button>
                        <button type="button" class="btn btn-label-secondary d-none" id="stopQrScanner">
                            <i class="bx bx-stop-circle me-1"></i> Tutup Kamera
                        </button>
                    </div>
                </div>

                <div id="qrScannerMessage" class="alert alert-info mb-3">
                    Kamera belum aktif.
                </div>

                <div id="qrScannerPanel" class="border rounded overflow-hidden bg-dark d-none" style="max-width: 520px;">
                    <video id="qrScannerVideo" class="w-100 d-block" playsinline muted style="aspect-ratio: 4 / 3; object-fit: cover;"></video>
                    <div id="html5QrReader" class="d-none bg-white"></div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
                    <span class="text-muted small me-1">Keterangan:</span>
                    <span class="badge bg-label-primary">H = Hadir</span>
                    <span class="badge bg-label-info">S = Sakit</span>
                    <span class="badge bg-label-danger">A = Alpa</span>
                    <span class="badge bg-label-warning">I = Izin</span>
                    <span class="badge bg-label-secondary">T = Terlambat</span>
                </div>

                <div class="table-responsive text-nowrap">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>Murid</th>
                                <th>NIS/NISN</th>
                                <th>Status</th>
                                <th>Catatan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($session->records->sortBy(fn ($record) => $record->student?->user?->name) as $record)
                                <tr data-attendance-student-id="{{ $record->student_id }}">
                                    <td>
                                        <strong>{{ $record->student?->user?->name }}</strong>
                                        <div class="text-muted small">{{ $record->student?->user?->email }}</div>
                                    </td>
                                    <td>
                                        <div>{{ $record->student?->nis ?: '-' }}</div>
                                        <div class="text-muted small">{{ $record->student?->nisn ? 'NISN: '.$record->student?->nisn : '' }}</div>
                                    </td>
                                    <td style="min-width: 180px;">
                                        @php
                                            $currentStatus = old("records.{$record->id}.status", $record->status);
                                            $statusShortLabels = [
                                                'present' => 'H',
                                                'sick' => 'S',
                                                'absent' => 'A',
                                                'permit' => 'I',
                                                'late' => 'T',
                                            ];
                                            $statusButtonClasses = [
                                                'present' => ['active' => 'btn-primary', 'inactive' => 'btn-label-primary'],
                                                'sick' => ['active' => 'btn-info', 'inactive' => 'btn-label-info'],
                                                'absent' => ['active' => 'btn-danger', 'inactive' => 'btn-label-danger'],
                                                'permit' => ['active' => 'btn-warning', 'inactive' => 'btn-label-warning'],
                                                'late' => ['active' => 'btn-secondary', 'inactive' => 'btn-label-secondary'],
                                            ];
                                            $statusButtonOrder = ['present', 'sick', 'absent', 'permit', 'late'];
                                        @endphp
                                        <input type="hidden" name="records[{{ $record->id }}][status]" value="{{ $currentStatus }}" data-attendance-status="{{ $record->student_id }}">
                                        <div class="attendance-status-group">
                                            @foreach($statusButtonOrder as $value)
                                                @php
                                                    $label = $recordStatuses[$value] ?? $value;
                                                    $activeClass = $statusButtonClasses[$value]['active'];
                                                    $inactiveClass = $statusButtonClasses[$value]['inactive'];
                                                @endphp
                                                <button
                                                    type="button"
                                                    @class([
                                                        'btn attendance-status-button',
                                                        $activeClass => $currentStatus === $value,
                                                        $inactiveClass => $currentStatus !== $value,
                                                    ])
                                                    data-attendance-status-button="{{ $record->student_id }}"
                                                    data-status-value="{{ $value }}"
                                                    data-active-class="{{ $activeClass }}"
                                                    data-inactive-class="{{ $inactiveClass }}"
                                                    title="{{ $label }}"
                                                    aria-label="{{ $label }}"
                                                    @disabled($session->status === 'locked')
                                                >
                                                    {{ $statusShortLabels[$value] ?? strtoupper(substr($label, 0, 1)) }}
                                                </button>
                                            @endforeach
                                        </div>
                                        <div class="text-muted small mt-1" data-attendance-checked-at="{{ $record->student_id }}">
                                            {{ $record->checked_at ? 'Scan: '.$record->checked_at->format('d M Y H:i:s') : 'Belum discan' }}
                                        </div>
                                    </td>
                                    <td style="min-width: 240px;">
                                        <input class="form-control" name="records[{{ $record->id }}][notes]" value="{{ old("records.{$record->id}.notes", $record->notes) }}" placeholder="Opsional" @disabled($session->status === 'locked')>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-5 text-muted">Belum ada murid pada sesi presensi ini.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="d-flex flex-wrap gap-2 mt-4">
                    @if($session->status !== 'locked')
                        <button type="submit" name="action" value="draft" class="btn btn-label-primary">
                            Simpan Draft
                        </button>
                        <button type="submit" name="action" value="submit" class="btn btn-primary">
                            Submit Presensi
                        </button>
                    @endif
                    <a href="{{ route('attendances.schedule') }}" class="btn btn-label-secondary">Kembali</a>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const startButton = document.getElementById('startQrScanner');
    const stopButton = document.getElementById('stopQrScanner');
    const panel = document.getElementById('qrScannerPanel');
    const video = document.getElementById('qrScannerVideo');
    const html5QrReader = document.getElementById('html5QrReader');
    const message = document.getElementById('qrScannerMessage');
    const scanUrl = @json(route('attendances.schedule.scan', $session));
    const csrfToken = @json(csrf_token());
    const isLocked = @json($session->status === 'locked');

    let stream = null;
    let detector = null;
    let scanning = false;
    let posting = false;
    let lastScanValue = '';
    let lastScanAt = 0;
    let html5QrCode = null;
    let scannerMode = null;

    const showMessage = (type, text) => {
        if (!message) {
            return;
        }

        message.className = 'alert alert-' + type + ' mb-3';
        message.textContent = text;
    };

    const stopScanner = async () => {
        scanning = false;

        if (stream) {
            stream.getTracks().forEach((track) => track.stop());
            stream = null;
        }

        if (video) {
            video.srcObject = null;
            video.classList.remove('d-none');
        }

        if (html5QrCode && scannerMode === 'html5') {
            try {
                await html5QrCode.stop();
                await html5QrCode.clear();
            } catch (error) {
                //
            }
        }

        html5QrCode = null;
        scannerMode = null;
        html5QrReader?.classList.add('d-none');

        panel?.classList.add('d-none');
        stopButton?.classList.add('d-none');
        startButton?.classList.remove('d-none');
    };

    const setAttendanceStatus = (studentId, status) => {
        const input = document.querySelector('[data-attendance-status="' + studentId + '"]');
        const buttons = document.querySelectorAll('[data-attendance-status-button="' + studentId + '"]');

        if (input) {
            input.value = status;
        }

        buttons.forEach((button) => {
            const isActive = button.dataset.statusValue === status;
            const activeClass = button.dataset.activeClass;
            const inactiveClass = button.dataset.inactiveClass;

            if (activeClass && inactiveClass) {
                button.classList.toggle(activeClass, isActive);
                button.classList.toggle(inactiveClass, !isActive);
            }
        });
    };

    const markStudentRow = (data) => {
        const studentId = data?.student?.id;

        if (!studentId) {
            return;
        }

        setAttendanceStatus(studentId, 'present');

        const row = document.querySelector('[data-attendance-student-id="' + studentId + '"]');
        const checkedAt = document.querySelector('[data-attendance-checked-at="' + studentId + '"]');

        if (checkedAt) {
            checkedAt.textContent = 'Scan: ' + (data.record?.checked_at || '-');
        }

        if (row) {
            row.classList.add('table-success');

            setTimeout(() => {
                row.classList.remove('table-success');
            }, 1800);
        }
    };

    const submitScan = async (rawValue) => {
        const now = Date.now();

        if (posting || (rawValue === lastScanValue && now - lastScanAt < 2500)) {
            return;
        }

        posting = true;
        lastScanValue = rawValue;
        lastScanAt = now;
        showMessage('info', 'Memproses QR Code...');

        try {
            const response = await fetch(scanUrl, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: JSON.stringify({
                    student_id: rawValue,
                }),
            });

            const data = await response.json();

            if (!response.ok) {
                showMessage(response.status === 422 ? 'warning' : 'danger', data.message || 'QR Code gagal diproses.');
                return;
            }

            markStudentRow(data);
            showMessage('success', data.message || 'Presensi berhasil diproses.');
        } catch (error) {
            showMessage('danger', 'Kamera berhasil membaca QR, tetapi data gagal dikirim ke server.');
        } finally {
            posting = false;
        }
    };

    const scanLoop = async () => {
        if (!scanning || !detector || !video) {
            return;
        }

        if (video.readyState >= HTMLMediaElement.HAVE_CURRENT_DATA) {
            try {
                const barcodes = await detector.detect(video);

                if (barcodes.length > 0 && barcodes[0].rawValue) {
                    await submitScan(barcodes[0].rawValue);
                }
            } catch (error) {
                showMessage('warning', 'Scanner belum bisa membaca frame kamera. Coba arahkan ulang QR Code.');
            }
        }

        if (scanning) {
            window.requestAnimationFrame(scanLoop);
        }
    };

    const loadHtml5Qrcode = () => new Promise((resolve, reject) => {
        if (window.Html5Qrcode) {
            resolve();
            return;
        }

        const existingScript = document.querySelector('script[data-html5-qrcode]');

        if (existingScript) {
            existingScript.addEventListener('load', resolve, { once: true });
            existingScript.addEventListener('error', reject, { once: true });
            return;
        }

        const script = document.createElement('script');
        script.src = 'https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js';
        script.async = true;
        script.dataset.html5Qrcode = 'true';
        script.addEventListener('load', resolve, { once: true });
        script.addEventListener('error', reject, { once: true });
        document.head.appendChild(script);
    });

    const startHtml5Scanner = async () => {
        await loadHtml5Qrcode();

        if (!window.Html5Qrcode || !html5QrReader) {
            throw new Error('html5-qrcode tidak tersedia.');
        }

        scannerMode = 'html5';
        scanning = true;
        video?.classList.add('d-none');
        html5QrReader.classList.remove('d-none');
        panel?.classList.remove('d-none');
        stopButton?.classList.remove('d-none');
        startButton?.classList.add('d-none');

        html5QrCode = new Html5Qrcode('html5QrReader');

        await html5QrCode.start(
            { facingMode: 'environment' },
            {
                fps: 10,
                qrbox: { width: 240, height: 240 },
                aspectRatio: 1.333334,
            },
            (decodedText) => {
                submitScan(decodedText);
            },
            () => {}
        );

        showMessage('info', 'Kamera aktif. Arahkan QR Code nametag murid ke kamera.');
    };

    startButton?.addEventListener('click', async () => {
        if (isLocked) {
            showMessage('warning', 'Presensi yang sudah dikunci tidak dapat discan.');
            return;
        }

        if (!navigator.mediaDevices?.getUserMedia) {
            showMessage('warning', 'Browser tidak dapat mengakses kamera. Buka lewat https:// atau localhost/127.0.0.1, lalu izinkan akses kamera.');
            return;
        }

        try {
            if (!('BarcodeDetector' in window)) {
                showMessage('info', 'Memuat scanner QR alternatif...');
                await startHtml5Scanner();
                return;
            }

            scannerMode = 'native';
            detector = new BarcodeDetector({ formats: ['qr_code'] });
            stream = await navigator.mediaDevices.getUserMedia({
                video: {
                    facingMode: 'environment',
                },
                audio: false,
            });

            video.srcObject = stream;
            await video.play();

            panel?.classList.remove('d-none');
            stopButton?.classList.remove('d-none');
            startButton?.classList.add('d-none');
            scanning = true;
            showMessage('info', 'Kamera aktif. Arahkan QR Code nametag murid ke kamera.');
            scanLoop();
        } catch (error) {
            await stopScanner();
            showMessage('danger', 'Kamera tidak dapat dibuka. Buka lewat https:// atau localhost/127.0.0.1, lalu cek izin kamera browser.');
        }
    });

    stopButton?.addEventListener('click', async () => {
        await stopScanner();
        showMessage('info', 'Kamera ditutup.');
    });

    document.querySelectorAll('[data-attendance-status-button]').forEach((button) => {
        button.addEventListener('click', () => {
            setAttendanceStatus(button.dataset.attendanceStatusButton, button.dataset.statusValue);
        });
    });

    window.addEventListener('beforeunload', stopScanner);
});
</script>
@endsection
