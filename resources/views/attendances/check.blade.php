@extends('layouts.app')

@section('content')
@php
    $canOpenAttendanceHub = in_array(\App\Support\EffectiveAccess::role(request()), ['school_admin', 'teacher'], true);
@endphp

<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 py-4 mb-2">
        <div>
            <h4 class="mb-1">Presensi Datang & Pulang</h4>
            <p class="text-muted mb-0">{{ $school->name }} - scan QR nametag murid tanpa memilih kelas.</p>
        </div>

        <a href="{{ $canOpenAttendanceHub ? route('attendances.index') : route('dashboard') }}" class="btn btn-label-secondary">
            <i class="bx bx-arrow-back me-1"></i> Kembali
        </a>
    </div>

    <div class="row g-4">
        <div class="col-lg-7">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-3">
                        <div>
                            <h5 class="mb-1">Mode Scan Otomatis</h5>
                            <div class="text-muted small">Scan pertama menjadi datang, scan berikutnya menjadi pulang.</div>
                        </div>
                        <div class="d-flex flex-wrap gap-2">
                            <button type="button" class="btn btn-label-primary" id="startQrScanner">
                                <i class="bx bx-camera me-1"></i> Buka Kamera
                            </button>
                            <button type="button" class="btn btn-label-secondary d-none" id="stopQrScanner">
                                <i class="bx bx-stop-circle me-1"></i> Tutup Kamera
                            </button>
                        </div>
                    </div>

                    <div id="qrScannerMessage" class="alert alert-info mb-3">
                        Memulai kamera...
                    </div>

                    <div id="qrScannerPanel" class="border rounded overflow-hidden bg-dark" style="max-width: 620px;">
                        <video id="qrScannerVideo" class="w-100 d-block" playsinline muted style="aspect-ratio: 4 / 3; object-fit: cover;"></video>
                        <div id="html5QrReader" class="d-none bg-white"></div>
                    </div>

                    <form class="mt-4" id="manualScanForm">
                        <label class="form-label" for="manualScanInput">Input Manual / Scanner Eksternal</label>
                        <div class="input-group">
                            <input class="form-control" id="manualScanInput" autocomplete="off" placeholder="Tempel UUID atau hasil scan QR">
                            <button class="btn btn-primary" type="submit">
                                <i class="bx bx-qr-scan me-1"></i> Proses
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card mb-4">
                <div class="card-body">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <span class="avatar rounded bg-label-primary">
                            <i class="bx bx-id-card"></i>
                        </span>
                        <div>
                            <h5 class="mb-0">Hasil Scan Terakhir</h5>
                            <div class="text-muted small">{{ now()->format('d M Y') }}</div>
                        </div>
                    </div>

                    <div id="lastScanEmpty" class="text-muted">Belum ada QR yang berhasil diproses.</div>

                    <div id="lastScanResult" class="d-none">
                        <div class="d-flex align-items-start justify-content-between gap-3 mb-3">
                            <div>
                                <h5 class="mb-1" id="lastStudentName">-</h5>
                                <div class="text-muted small" id="lastStudentMeta">-</div>
                            </div>
                            <span class="badge" id="lastActionBadge">-</span>
                        </div>
                        <div class="row g-3">
                            <div class="col-6">
                                <div class="text-muted small">Datang</div>
                                <strong id="lastCheckIn">-</strong>
                            </div>
                            <div class="col-6">
                                <div class="text-muted small">Pulang</div>
                                <strong id="lastCheckOut">-</strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <h5 class="mb-3">Riwayat Hari Ini</h5>
                    <div class="list-group list-group-flush" id="todayAttendanceList">
                        @forelse($todayAttendances as $attendance)
                            <div class="list-group-item px-0" data-today-attendance-id="{{ $attendance->id }}">
                                <div class="d-flex align-items-start justify-content-between gap-3">
                                    <div>
                                        <strong>{{ $attendance->student?->user?->name ?? '-' }}</strong>
                                        <div class="text-muted small">
                                            {{ $attendance->student?->nis ?: '-' }}
                                            @if($attendance->student?->nisn)
                                                / {{ $attendance->student?->nisn }}
                                            @endif
                                        </div>
                                    </div>
                                    <span @class([
                                        'badge',
                                        'bg-label-success' => $attendance->check_in_at && $attendance->check_out_at,
                                        'bg-label-primary' => $attendance->check_in_at && ! $attendance->check_out_at,
                                    ])>
                                        {{ $attendance->check_out_at ? 'Lengkap' : 'Datang' }}
                                    </span>
                                </div>
                                <div class="d-flex flex-wrap gap-3 text-muted small mt-2">
                                    <span>Datang: {{ $attendance->check_in_at?->format('H:i:s') ?? '-' }}</span>
                                    <span>Pulang: {{ $attendance->check_out_at?->format('H:i:s') ?? '-' }}</span>
                                </div>
                            </div>
                        @empty
                            <div class="text-muted" id="todayAttendanceEmpty">Belum ada presensi datang/pulang hari ini.</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const startButton = document.getElementById('startQrScanner');
    const stopButton = document.getElementById('stopQrScanner');
    const panel = document.getElementById('qrScannerPanel');
    const video = document.getElementById('qrScannerVideo');
    const html5QrReader = document.getElementById('html5QrReader');
    const message = document.getElementById('qrScannerMessage');
    const manualForm = document.getElementById('manualScanForm');
    const manualInput = document.getElementById('manualScanInput');
    const scanUrl = @json(route('attendances.check.scan'));
    const csrfToken = @json(csrf_token());

    let stream = null;
    let detector = null;
    let scanning = false;
    let posting = false;
    let lastScanValue = '';
    let lastScanAt = 0;
    let html5QrCode = null;
    let scannerMode = null;

    const showMessage = (type, text) => {
        message.className = 'alert alert-' + type + ' mb-3';
        message.textContent = text;
    };

    const vibrate = (pattern) => {
        if (navigator.vibrate) {
            navigator.vibrate(pattern);
        }
    };

    const updateLastScan = (data) => {
        document.getElementById('lastScanEmpty')?.classList.add('d-none');
        document.getElementById('lastScanResult')?.classList.remove('d-none');
        document.getElementById('lastStudentName').textContent = data.student?.name || '-';
        document.getElementById('lastStudentMeta').textContent = [
            data.student?.nis ? 'NIS: ' + data.student.nis : null,
            data.student?.nisn ? 'NISN: ' + data.student.nisn : null,
            data.student?.classroom ? 'Rombel: ' + data.student.classroom : null,
        ].filter(Boolean).join(' | ') || '-';
        document.getElementById('lastCheckIn').textContent = data.attendance?.check_in_at || '-';
        document.getElementById('lastCheckOut').textContent = data.attendance?.check_out_at || '-';

        const badge = document.getElementById('lastActionBadge');
        badge.className = 'badge ' + (data.action === 'check_out' ? 'bg-label-success' : data.action === 'check_in' ? 'bg-label-primary' : 'bg-label-secondary');
        badge.textContent = data.action === 'check_out' ? 'Pulang' : data.action === 'check_in' ? 'Datang' : 'Lengkap';
    };

    const prependTodayAttendance = (data) => {
        const list = document.getElementById('todayAttendanceList');

        if (!list || !data.attendance?.id) {
            return;
        }

        document.getElementById('todayAttendanceEmpty')?.remove();

        const existing = list.querySelector('[data-today-attendance-id="' + data.attendance.id + '"]');
        existing?.remove();

        const item = document.createElement('div');
        item.className = 'list-group-item px-0';
        item.dataset.todayAttendanceId = data.attendance.id;
        item.innerHTML = `
            <div class="d-flex align-items-start justify-content-between gap-3">
                <div>
                    <strong></strong>
                    <div class="text-muted small"></div>
                </div>
                <span class="badge"></span>
            </div>
            <div class="d-flex flex-wrap gap-3 text-muted small mt-2">
                <span class="check-in"></span>
                <span class="check-out"></span>
            </div>
        `;

        item.querySelector('strong').textContent = data.student?.name || '-';
        item.querySelector('.text-muted.small').textContent = [data.student?.nis || '-', data.student?.nisn].filter(Boolean).join(' / ');
        item.querySelector('.badge').className = 'badge ' + (data.attendance?.check_out_at ? 'bg-label-success' : 'bg-label-primary');
        item.querySelector('.badge').textContent = data.attendance?.check_out_at ? 'Lengkap' : 'Datang';
        item.querySelector('.check-in').textContent = 'Datang: ' + (data.attendance?.check_in_at || '-');
        item.querySelector('.check-out').textContent = 'Pulang: ' + (data.attendance?.check_out_at || '-');
        list.prepend(item);
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
        stopButton?.classList.add('d-none');
        startButton?.classList.remove('d-none');
    };

    const submitScan = async (rawValue) => {
        const value = String(rawValue || '').trim();
        const now = Date.now();

        if (!value || posting || (value === lastScanValue && now - lastScanAt < 2500)) {
            return;
        }

        posting = true;
        lastScanValue = value;
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
                    student_id: value,
                }),
            });

            const data = await response.json();

            if (!response.ok) {
                vibrate([120, 80, 120]);
                showMessage(response.status === 422 ? 'warning' : 'danger', data.message || 'QR Code gagal diproses.');
                return;
            }

            vibrate(90);
            updateLastScan(data);
            prependTodayAttendance(data);
            showMessage(data.action === 'complete' ? 'warning' : 'success', data.message || 'Presensi berhasil diproses.');
        } catch (error) {
            vibrate([120, 80, 120]);
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
                //
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
                qrbox: { width: 250, height: 250 },
                aspectRatio: 1.333334,
            },
            (decodedText) => {
                submitScan(decodedText);
            },
            () => {}
        );

        showMessage('info', 'Kamera aktif. Arahkan QR Code nametag murid ke kamera.');
    };

    const startScanner = async () => {
        if (!navigator.mediaDevices?.getUserMedia) {
            showMessage('warning', 'Browser tidak dapat mengakses kamera. Gunakan https:// atau localhost/127.0.0.1, lalu izinkan akses kamera.');
            manualInput?.focus();
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
            showMessage('danger', 'Kamera tidak dapat dibuka. Cek izin kamera browser atau gunakan input manual.');
            manualInput?.focus();
        }
    };

    startButton?.addEventListener('click', startScanner);
    stopButton?.addEventListener('click', async () => {
        await stopScanner();
        showMessage('info', 'Kamera ditutup.');
    });

    manualForm?.addEventListener('submit', async (event) => {
        event.preventDefault();
        await submitScan(manualInput?.value);
        if (manualInput) {
            manualInput.value = '';
            manualInput.focus();
        }
    });

    window.addEventListener('beforeunload', stopScanner);
    startScanner();
});
</script>
@endsection
