@extends('layouts.app')

@section('content')
@php
    $canOpenAttendanceHub = in_array(\App\Support\EffectiveAccess::role(request()), ['school_admin', 'teacher'], true);
    $dailyCheckInTime = substr($school->daily_check_in_time ?? '07:00:00', 0, 5);
    $dailyCheckOutTime = substr($school->daily_check_out_time ?? '14:00:00', 0, 5);
    $dailyLateTolerance = $school->daily_late_tolerance_minutes ?? 10;
    $dailyEarlyLeaveTolerance = $school->daily_early_leave_tolerance_minutes ?? 0;
    $dailyMinCheckout = $school->daily_min_checkout_minutes ?? 60;
@endphp

<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 py-4 mb-2">
        <div>
            <h4 class="mb-1">Presensi Harian</h4>
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
                            <button type="button" class="btn btn-label-success" id="enableSoundButton">
                                <i class="bx bx-volume-full me-1"></i> Aktifkan Suara
                            </button>
                            <button type="button" class="btn btn-label-primary" id="startQrScanner">
                                <i class="bx bx-camera me-1"></i> Buka Kamera
                            </button>
                            <button type="button" class="btn btn-label-secondary d-none" id="stopQrScanner">
                                <i class="bx bx-stop-circle me-1"></i> Tutup Kamera
                            </button>
                        </div>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-6 col-md-3">
                            <div class="border rounded p-2 h-100">
                                <div class="text-muted small">Jam Masuk</div>
                                <strong>{{ $dailyCheckInTime }}</strong>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="border rounded p-2 h-100">
                                <div class="text-muted small">Toleransi</div>
                                <strong>{{ $dailyLateTolerance }} menit</strong>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="border rounded p-2 h-100">
                                <div class="text-muted small">Jam Pulang</div>
                                <strong>{{ $dailyCheckOutTime }}</strong>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="border rounded p-2 h-100">
                                <div class="text-muted small">Scan Pulang</div>
                                <strong>{{ $dailyMinCheckout }} menit</strong>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="text-muted small">
                                Pulang sebelum {{ $dailyEarlyLeaveTolerance }} menit dari jam pulang akan ditandai pulang cepat.
                            </div>
                        </div>
                    </div>

                    <div @class(['alert mb-3', 'alert-info' => $isAttendanceDay, 'alert-warning' => ! $isAttendanceDay])>
                        <strong>{{ $isAttendanceDay ? 'Hari presensi aktif' : 'Bukan hari presensi' }}</strong>
                        <span class="d-block small">{{ $attendanceDayContext['message'] }} Hari sekolah aktif: {{ implode(', ', $schoolAttendanceDayLabels) }}.</span>
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

                    <div class="border rounded p-3 mt-4">
                        <div class="d-flex align-items-center gap-2 mb-3">
                            <span class="avatar avatar-sm rounded bg-label-warning">
                                <i class="bx bx-user-x"></i>
                            </span>
                            <div>
                                <h6 class="mb-0">Tandai Tidak Hadir</h6>
                                <div class="text-muted small">Gunakan untuk murid yang sakit, izin, atau alpa tanpa scan QR.</div>
                            </div>
                        </div>
                        <form id="manualAbsenceForm">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label" for="manualAbsenceStudent">Murid</label>
                                    <select class="form-select select2" id="manualAbsenceStudent" name="student_id" data-placeholder="Cari nama murid atau rombel" required>
                                        <option value="">Pilih murid</option>
                                        @foreach($activeStudents as $student)
                                            <option value="{{ $student->id }}">
                                                {{ $student->user?->name ?? '-' }} - {{ $student->classrooms->first()?->name ?? 'Tanpa rombel aktif' }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label" for="manualAbsenceStatus">Status</label>
                                    <select class="form-select" id="manualAbsenceStatus" name="status" required>
                                        <option value="">Pilih status</option>
                                        <option value="sick">Sakit</option>
                                        <option value="permit">Izin</option>
                                        <option value="absent">Alpa</option>
                                    </select>
                                </div>
                                <div class="col-12">
                                    <label class="form-label" for="manualAbsenceNotes">Catatan</label>
                                    <textarea class="form-control" id="manualAbsenceNotes" name="notes" rows="2" maxlength="1000" placeholder="Opsional, contoh: surat sakit menyusul"></textarea>
                                </div>
                                <div class="col-12">
                                    <button class="btn btn-warning" type="submit">
                                        <i class="bx bx-save me-1"></i> Simpan Status
                                    </button>
                                    <span class="form-text ms-2">Jika murid sudah scan datang, status ini tidak akan diterapkan.</span>
                                </div>
                            </div>
                        </form>
                    </div>
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
                        <div class="mt-3" id="lastStatusNote"></div>
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
                                        'bg-label-info' => $attendance->status === 'sick',
                                        'bg-label-warning' => $attendance->status === 'permit',
                                        'bg-label-danger' => $attendance->status === 'absent',
                                    ])>
                                        @if($attendance->check_in_at || $attendance->check_out_at)
                                            {{ $attendance->check_out_at ? 'Lengkap' : 'Datang' }}
                                        @else
                                            {{ ['sick' => 'Sakit', 'permit' => 'Izin', 'absent' => 'Alpa'][$attendance->status] ?? 'Belum Hadir' }}
                                        @endif
                                    </span>
                                </div>
                                <div class="d-flex flex-wrap gap-3 text-muted small mt-2">
                                    <span>
                                        Datang: {{ $attendance->check_in_at?->format('H:i:s') ?? '-' }}
                                        @if($attendance->check_in_status === 'late')
                                            <span class="badge bg-label-warning ms-1">Terlambat {{ $attendance->late_minutes }} menit</span>
                                        @elseif($attendance->check_in_status === 'on_time')
                                            <span class="badge bg-label-success ms-1">Tepat waktu</span>
                                        @endif
                                    </span>
                                    <span>
                                        Pulang: {{ $attendance->check_out_at?->format('H:i:s') ?? '-' }}
                                        @if($attendance->check_out_status === 'early')
                                            <span class="badge bg-label-warning ms-1">Pulang cepat {{ $attendance->early_leave_minutes }} menit</span>
                                        @elseif($attendance->check_out_status === 'normal')
                                            <span class="badge bg-label-success ms-1">Normal</span>
                                        @endif
                                    </span>
                                    @if(! $attendance->check_in_at && ! $attendance->check_out_at && in_array($attendance->status, ['sick', 'permit', 'absent'], true))
                                        <span>Catatan: {{ $attendance->notes ?: '-' }}</span>
                                    @endif
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
    const enableSoundButton = document.getElementById('enableSoundButton');
    const panel = document.getElementById('qrScannerPanel');
    const video = document.getElementById('qrScannerVideo');
    const html5QrReader = document.getElementById('html5QrReader');
    const message = document.getElementById('qrScannerMessage');
    const manualForm = document.getElementById('manualScanForm');
    const manualInput = document.getElementById('manualScanInput');
    const manualAbsenceForm = document.getElementById('manualAbsenceForm');
    const manualAbsenceStudent = document.getElementById('manualAbsenceStudent');
    const manualAbsenceStatus = document.getElementById('manualAbsenceStatus');
    const manualAbsenceNotes = document.getElementById('manualAbsenceNotes');
    const scanUrl = @json(route('attendances.check.scan', [], false));
    const manualUrl = @json(route('attendances.check.manual', [], false));
    const csrfToken = @json(csrf_token());

    let stream = null;
    let detector = null;
    let scanning = false;
    let posting = false;
    let lastScanValue = '';
    let lastScanAt = 0;
    let html5QrCode = null;
    let scannerMode = null;
    let audioContext = null;
    let soundEnabled = false;

    const showMessage = (type, text) => {
        message.className = 'alert alert-' + type + ' mb-3';
        message.textContent = text;
    };

    const ensureAudio = async (playProbe = false) => {
        const AudioContextClass = window.AudioContext || window.webkitAudioContext;

        if (!AudioContextClass) {
            return null;
        }

        if (!audioContext) {
            audioContext = new AudioContextClass();
        }

        if (audioContext.state === 'suspended') {
            try {
                await audioContext.resume();
            } catch (error) {
                //
            }
        }

        soundEnabled = audioContext.state === 'running';

        if (soundEnabled && playProbe) {
            playTone([
                { frequency: 1046, startsAt: 0, duration: 0.07 },
            ], 0.12);
        }

        if (enableSoundButton) {
            enableSoundButton.classList.toggle('d-none', soundEnabled);
        }

        return audioContext;
    };

    const playTone = (tones, volume = 0.28) => {
        if (!audioContext || audioContext.state !== 'running') {
            return;
        }

        const now = audioContext.currentTime;
        const gain = audioContext.createGain();

        gain.gain.setValueAtTime(0.0001, now);
        gain.gain.exponentialRampToValueAtTime(volume, now + 0.02);
        gain.gain.exponentialRampToValueAtTime(0.0001, now + 0.45);
        gain.connect(audioContext.destination);

        tones.forEach((tone) => {
            const oscillator = audioContext.createOscillator();
            const start = now + tone.startsAt;
            const stop = start + tone.duration;

            oscillator.type = 'triangle';
            oscillator.frequency.setValueAtTime(tone.frequency, start);
            oscillator.connect(gain);
            oscillator.start(start);
            oscillator.stop(stop);
        });
    };

    const playSuccessSound = async () => {
        await ensureAudio();

        if (!soundEnabled) {
            return;
        }

        playTone([
            { frequency: 880, startsAt: 0, duration: 0.12 },
            { frequency: 1175, startsAt: 0.13, duration: 0.18 },
        ]);
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
        badge.className = 'badge ' + actionBadgeClass(data);
        badge.textContent = actionLabel(data);

        const statusNote = document.getElementById('lastStatusNote');

        if (statusNote) {
            const notes = [];

            if (data.action === 'manual_status') {
                notes.push('Status: ' + (data.attendance?.status_label || '-'));

                if (data.attendance?.notes) {
                    notes.push('Catatan: ' + data.attendance.notes);
                }
            }

            if (data.attendance?.check_in_status_label) {
                notes.push('Datang: ' + data.attendance.check_in_status_label + (data.attendance.late_minutes ? ' ' + data.attendance.late_minutes + ' menit' : ''));
            }

            if (data.attendance?.check_out_status_label) {
                notes.push('Pulang: ' + data.attendance.check_out_status_label + (data.attendance.early_leave_minutes ? ' ' + data.attendance.early_leave_minutes + ' menit' : ''));
            }

            if (data.attendance?.can_checkout_after) {
                notes.push('Pulang dapat dicatat setelah ' + data.attendance.can_checkout_after);
            }

            statusNote.className = notes.length ? 'alert alert-secondary mb-0 py-2' : '';
            statusNote.textContent = notes.join(' | ');
        }
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
        item.querySelector('.badge').className = 'badge ' + actionBadgeClass(data);
        item.querySelector('.badge').textContent = actionLabel(data);
        item.querySelector('.check-in').textContent = 'Datang: ' + (data.attendance?.check_in_at || '-') + dailyStatusText(data.attendance?.check_in_status_label, data.attendance?.late_minutes);
        item.querySelector('.check-out').textContent = 'Pulang: ' + (data.attendance?.check_out_at || '-') + dailyStatusText(data.attendance?.check_out_status_label, data.attendance?.early_leave_minutes);

        if (data.action === 'manual_status') {
            item.querySelector('.check-in').textContent = 'Status: ' + (data.attendance?.status_label || '-');
            item.querySelector('.check-out').textContent = 'Catatan: ' + (data.attendance?.notes || '-');
        }

        list.prepend(item);
    };

    const actionBadgeClass = (data) => {
        if (data.action === 'manual_status') {
            return data.attendance?.status === 'sick'
                ? 'bg-label-info'
                : data.attendance?.status === 'permit'
                    ? 'bg-label-warning'
                    : 'bg-label-danger';
        }

        return data.action === 'check_out'
            ? 'bg-label-success'
            : data.action === 'check_in'
                ? 'bg-label-primary'
                : data.action === 'duplicate_check_in'
                    ? 'bg-label-warning'
                    : 'bg-label-secondary';
    };

    const actionLabel = (data) => {
        if (data.action === 'manual_status') {
            return data.attendance?.status_label || 'Tidak Hadir';
        }

        return data.action === 'check_out'
            ? 'Pulang'
            : data.action === 'check_in'
                ? 'Datang'
                : data.action === 'duplicate_check_in'
                    ? 'Sudah Datang'
                    : 'Lengkap';
    };

    const dailyStatusText = (label, minutes) => {
        if (!label) {
            return '';
        }

        return ' (' + label + (minutes ? ' ' + minutes + ' menit' : '') + ')';
    };

    const readScanResponse = async (response) => {
        const contentType = response.headers.get('content-type') || '';

        if (contentType.includes('application/json')) {
            return await response.json();
        }

        await response.text();

        if (response.status === 419) {
            return { message: 'Sesi halaman kedaluwarsa. Muat ulang halaman lalu scan ulang QR.' };
        }

        if (response.status === 401 || response.redirected || response.url.includes('/login')) {
            return { message: 'Sesi login tidak aktif. Silakan login ulang lalu buka scanner kembali.' };
        }

        if (response.status === 403) {
            return { message: 'Akun ini tidak memiliki akses untuk memproses scan presensi.' };
        }

        return { message: 'Server mengembalikan respons tidak valid (HTTP ' + response.status + '). Cek log Laravel untuk detail error.' };
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
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify({
                    student_id: value,
                }),
            });

            const data = await readScanResponse(response);

            if (!response.ok) {
                vibrate([120, 80, 120]);
                showMessage(response.status === 422 ? 'warning' : 'danger', data.message || 'QR Code gagal diproses.');
                return;
            }

            vibrate(90);
            await playSuccessSound();
            updateLastScan(data);
            prependTodayAttendance(data);
            showMessage(['complete', 'duplicate_check_in'].includes(data.action) ? 'warning' : 'success', data.message || 'Presensi berhasil diproses.');
        } catch (error) {
            vibrate([120, 80, 120]);
            showMessage('danger', 'Kamera berhasil membaca QR, tetapi request gagal dikirim. ' + (error.message || 'Cek koneksi dan domain aplikasi.'));
        } finally {
            posting = false;
        }
    };

    const submitManualAbsence = async () => {
        const studentId = manualAbsenceStudent?.value;
        const status = manualAbsenceStatus?.value;
        const notes = manualAbsenceNotes?.value || '';

        if (!studentId || !status) {
            showMessage('warning', 'Pilih murid dan status tidak hadir terlebih dahulu.');
            return;
        }

        showMessage('info', 'Menyimpan status tidak hadir...');

        try {
            const response = await fetch(manualUrl, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify({
                    student_id: studentId,
                    status: status,
                    notes: notes,
                }),
            });

            const data = await readScanResponse(response);

            if (!response.ok) {
                showMessage(response.status === 422 ? 'warning' : 'danger', data.message || 'Status tidak hadir gagal disimpan.');
                return;
            }

            updateLastScan(data);
            prependTodayAttendance(data);
            showMessage('success', data.message || 'Status tidak hadir berhasil disimpan.');
            manualAbsenceForm?.reset();
        } catch (error) {
            showMessage('danger', 'Status tidak hadir gagal dikirim. ' + (error.message || 'Cek koneksi dan domain aplikasi.'));
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
        await ensureAudio();

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
    enableSoundButton?.addEventListener('click', async () => {
        await ensureAudio(true);
        showMessage(soundEnabled ? 'success' : 'warning', soundEnabled ? 'Suara aktif. Scan berhasil akan membunyikan nada.' : 'Browser belum mengizinkan suara. Coba tap tombol ini sekali lagi.');
    });
    stopButton?.addEventListener('click', async () => {
        await stopScanner();
        showMessage('info', 'Kamera ditutup.');
    });

    manualForm?.addEventListener('submit', async (event) => {
        event.preventDefault();
        await ensureAudio();
        await submitScan(manualInput?.value);
        if (manualInput) {
            manualInput.value = '';
            manualInput.focus();
        }
    });
    manualAbsenceForm?.addEventListener('submit', async (event) => {
        event.preventDefault();
        await submitManualAbsence();
    });

    window.addEventListener('beforeunload', stopScanner);
    window.addEventListener('pointerdown', () => {
        ensureAudio();
    }, { once: true });
    startScanner();
});
</script>
@endsection
