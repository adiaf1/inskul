@extends('layouts.app')

@php
    $statusLabels = [
        'hadir' => 'Hadir',
        'terlambat' => 'Terlambat',
        'pulang' => 'Pulang',
        'pulang_cepat' => 'Pulang Cepat',
        'di_luar_area' => 'Di Luar Area',
        'perlu_verifikasi' => 'Perlu Verifikasi',
    ];
@endphp

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    @if(session('success') || $errors->any())
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                Swal.fire({
                    title: @json(session('success') ? 'Sukses!' : 'Error!'),
                    text: @json(session('success') ?: $errors->first()),
                    icon: @json(session('success') ? 'success' : 'error'),
                    confirmButtonText: 'OK'
                });
            });
        </script>
    @endif

    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 py-4 mb-2">
        <div>
            <h4 class="mb-1">Presensi Guru</h4>
            <p class="text-muted mb-0">{{ $school->name }} - presensi datang dan pulang dengan selfie dan lokasi.</p>
        </div>
        <a href="{{ route('dashboard') }}" class="btn btn-label-secondary">Kembali</a>
    </div>

    <div class="row g-4">
        <div class="col-lg-5">
            <div class="card">
                <div class="card-body">
                    <h6 class="mb-3">Status Hari Ini</h6>
                    <div class="d-flex flex-column gap-3">
                        <div class="border rounded p-3">
                            <div class="text-muted small">Datang</div>
                            <div class="fw-semibold">{{ $attendance?->check_in_at?->format('H:i') ?? 'Belum presensi' }}</div>
                            <span class="badge bg-label-primary mt-2">{{ $statusLabels[$attendance?->check_in_status] ?? '-' }}</span>
                        </div>
                        <div class="border rounded p-3">
                            <div class="text-muted small">Pulang</div>
                            <div class="fw-semibold">{{ $attendance?->check_out_at?->format('H:i') ?? 'Belum presensi' }}</div>
                            <span class="badge bg-label-primary mt-2">{{ $statusLabels[$attendance?->check_out_status] ?? '-' }}</span>
                        </div>
                    </div>
                    <div class="alert alert-info mt-4 mb-0">
                        Aktifkan GPS, izinkan lokasi, lalu ambil foto selfie sebelum menyimpan presensi.
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-7">
            <div class="card">
                <div class="card-body">
                    <form method="POST" action="{{ route('teacher-attendances.store') }}" enctype="multipart/form-data" id="teacherAttendanceForm">
                        @csrf
                        <input type="hidden" name="type" id="attendance_type" value="check_in">
                        <input type="hidden" name="latitude" id="latitude">
                        <input type="hidden" name="longitude" id="longitude">
                        <input type="hidden" name="accuracy" id="accuracy">
                        <input type="file" class="d-none" id="photo" name="photo" accept="image/*" required>

                        <div class="mb-4">
                            <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-3">
                                <div>
                                    <h6 class="mb-1">Kamera Selfie</h6>
                                    <div class="text-muted small" id="cameraStatus">Memulai kamera depan...</div>
                                </div>
                                <div class="d-flex flex-wrap gap-2">
                                    <button type="button" class="btn btn-label-primary" id="startSelfieCamera">
                                        <i class="bx bx-camera me-1"></i> Buka Kamera
                                    </button>
                                    <button type="button" class="btn btn-label-secondary d-none" id="stopSelfieCamera">
                                        <i class="bx bx-stop-circle me-1"></i> Tutup Kamera
                                    </button>
                                </div>
                            </div>
                            <div class="border rounded overflow-hidden bg-dark">
                                <video id="selfieVideo" class="w-100 d-block" playsinline muted autoplay style="aspect-ratio: 3 / 4; object-fit: cover; transform: scaleX(-1);"></video>
                                <canvas id="selfieCanvas" class="d-none"></canvas>
                            </div>
                        </div>

                        <div class="border rounded p-3 mb-4">
                            <div class="d-flex align-items-center justify-content-between gap-3">
                                <div>
                                    <div class="fw-semibold">Lokasi</div>
                                    <div class="text-muted small" id="locationStatus">Lokasi belum diambil.</div>
                                </div>
                                <button type="button" class="btn btn-label-primary" onclick="requestTeacherLocation()">
                                    <i class="bx bx-current-location me-1"></i> Ambil Lokasi
                                </button>
                            </div>
                        </div>

                        <div class="d-flex flex-wrap gap-2">
                            <button type="button" class="btn btn-primary" onclick="submitTeacherAttendance('check_in')" @disabled($attendance?->check_in_at)>
                                <i class="bx bx-log-in-circle me-1"></i> Presensi Datang
                            </button>
                            <button type="button" class="btn btn-label-success" onclick="submitTeacherAttendance('check_out')" @disabled(! $attendance?->check_in_at || $attendance?->check_out_at)>
                                <i class="bx bx-log-out-circle me-1"></i> Presensi Pulang
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h6 class="mb-3">Riwayat 7 Hari Terakhir</h6>
                    <div class="table-responsive text-nowrap">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Tanggal</th>
                                    <th>Datang</th>
                                    <th>Pulang</th>
                                    <th>Jarak</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($history as $record)
                                    <tr>
                                        <td>{{ $record->attendance_date->format('d-m-Y') }}</td>
                                        <td>{{ $record->check_in_at?->format('H:i') ?? '-' }} <span class="text-muted small">{{ $statusLabels[$record->check_in_status] ?? '' }}</span></td>
                                        <td>{{ $record->check_out_at?->format('H:i') ?? '-' }} <span class="text-muted small">{{ $statusLabels[$record->check_out_status] ?? '' }}</span></td>
                                        <td>{{ $record->check_in_distance_meters !== null ? $record->check_in_distance_meters.' m' : '-' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-4">Belum ada riwayat presensi.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('teacherAttendanceForm');
    const video = document.getElementById('selfieVideo');
    const canvas = document.getElementById('selfieCanvas');
    const photoInput = document.getElementById('photo');
    const cameraStatus = document.getElementById('cameraStatus');
    const startButton = document.getElementById('startSelfieCamera');
    const stopButton = document.getElementById('stopSelfieCamera');
    let stream = null;

    const showCameraStatus = (text) => {
        if (cameraStatus) {
            cameraStatus.textContent = text;
        }
    };

    const startCamera = async () => {
        if (!navigator.mediaDevices?.getUserMedia) {
            showCameraStatus('Browser tidak dapat mengakses kamera. Buka lewat https:// atau localhost/127.0.0.1.');
            return false;
        }

        try {
            if (stream) {
                return true;
            }

            showCameraStatus('Meminta izin kamera depan...');
            stream = await navigator.mediaDevices.getUserMedia({
                video: {
                    facingMode: 'user',
                    width: { ideal: 960 },
                    height: { ideal: 1280 },
                },
                audio: false,
            });

            video.srcObject = stream;
            await video.play();
            startButton?.classList.add('d-none');
            stopButton?.classList.remove('d-none');
            showCameraStatus('Kamera depan aktif. Pastikan wajah terlihat jelas.');

            return true;
        } catch (error) {
            showCameraStatus('Kamera tidak dapat dibuka. Izinkan akses kamera pada browser.');
            return false;
        }
    };

    const stopCamera = () => {
        if (stream) {
            stream.getTracks().forEach((track) => track.stop());
            stream = null;
        }

        if (video) {
            video.srcObject = null;
        }

        startButton?.classList.remove('d-none');
        stopButton?.classList.add('d-none');
        showCameraStatus('Kamera ditutup.');
    };

    const captureSelfie = () => new Promise((resolve, reject) => {
        if (!stream || !video.videoWidth || !video.videoHeight) {
            reject(new Error('Kamera belum siap.'));
            return;
        }

        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;
        const context = canvas.getContext('2d');
        context.translate(canvas.width, 0);
        context.scale(-1, 1);
        context.drawImage(video, 0, 0, canvas.width, canvas.height);

        canvas.toBlob((blob) => {
            if (!blob) {
                reject(new Error('Foto selfie gagal dibuat.'));
                return;
            }

            const file = new File([blob], 'selfie-presensi-guru.jpg', { type: 'image/jpeg' });
            const transfer = new DataTransfer();
            transfer.items.add(file);
            photoInput.files = transfer.files;
            resolve();
        }, 'image/jpeg', 0.9);
    });

    window.requestTeacherLocation = function () {
        const status = document.getElementById('locationStatus');

        if (!navigator.geolocation) {
            status.textContent = 'Browser tidak mendukung geolocation.';
            return Promise.resolve(false);
        }

        status.textContent = 'Mengambil lokasi...';

        return new Promise((resolve) => {
            navigator.geolocation.getCurrentPosition(function (position) {
                document.getElementById('latitude').value = position.coords.latitude;
                document.getElementById('longitude').value = position.coords.longitude;
                document.getElementById('accuracy').value = Math.round(position.coords.accuracy || 0);
                status.textContent = 'Lokasi siap. Akurasi sekitar ' + Math.round(position.coords.accuracy || 0) + ' meter.';
                resolve(true);
            }, function () {
                status.textContent = 'Lokasi gagal diambil. Izinkan akses lokasi pada browser.';
                resolve(false);
            }, {
                enableHighAccuracy: true,
                timeout: 15000,
                maximumAge: 0
            });
        });
    };

    window.submitTeacherAttendance = async function (type) {
        if (!document.getElementById('latitude').value || !document.getElementById('longitude').value) {
            const locationReady = await window.requestTeacherLocation();

            if (!locationReady) {
                Swal.fire('Lokasi dibutuhkan', 'Izinkan lokasi terlebih dahulu, lalu tekan tombol presensi lagi.', 'warning');
                return;
            }
        }

        if (!stream) {
            const cameraReady = await startCamera();

            if (!cameraReady) {
                Swal.fire('Kamera dibutuhkan', 'Izinkan kamera depan terlebih dahulu, lalu tekan tombol presensi lagi.', 'warning');
                return;
            }
        }

        try {
            await captureSelfie();
        } catch (error) {
            Swal.fire('Selfie gagal', 'Kamera belum siap mengambil foto. Coba tekan tombol presensi lagi.', 'warning');
            return;
        }

        document.getElementById('attendance_type').value = type;
        form.submit();
    };

    startButton?.addEventListener('click', startCamera);
    stopButton?.addEventListener('click', stopCamera);
    window.addEventListener('beforeunload', stopCamera);

    window.requestTeacherLocation();
    startCamera();
});

</script>
@endsection
