@extends('layouts.app')

@section('content')
@php
    $schoolAttendanceDays = old('school_attendance_days', $school->school_attendance_days ?? [1, 2, 3, 4, 5, 6]);
    $schoolAttendanceDays = collect($schoolAttendanceDays)->map(fn ($day) => (int) $day)->all();
    $teacherAttendanceLatitude = old('teacher_attendance_latitude', $school->teacher_attendance_latitude);
    $teacherAttendanceLongitude = old('teacher_attendance_longitude', $school->teacher_attendance_longitude);
    $teacherAttendanceRadius = old('teacher_attendance_radius_meters', $school->teacher_attendance_radius_meters ?? 150);
    $schoolDayLabels = [
        1 => 'Senin',
        2 => 'Selasa',
        3 => 'Rabu',
        4 => 'Kamis',
        5 => 'Jumat',
        6 => 'Sabtu',
        7 => 'Minggu',
    ];
@endphp

<link rel="stylesheet" href="{{ asset('assets/vendor/libs/leaflet/leaflet.css') }}">

<style>
    .school-location-map {
        min-height: 320px;
        height: 42vh;
        max-height: 460px;
        width: 100%;
        border-radius: .375rem;
        border: 1px solid var(--bs-border-color);
        overflow: hidden;
    }

    .school-location-map .leaflet-control-attribution {
        font-size: .6875rem;
    }

    .school-location-map-status {
        min-height: 1.25rem;
    }
</style>

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

    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 py-4 mb-2">
        <div>
            <h4 class="mb-1">Profil Sekolah</h4>
            <p class="text-muted mb-0">Lihat dan perbarui identitas sekolah Anda.</p>
        </div>
        <a href="{{ route('dashboard') }}" class="btn btn-label-secondary">Kembali</a>
    </div>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('school-profile.update') }}" enctype="multipart/form-data">
                @csrf
                @method('PATCH')

                <div class="row g-4">
                    <div class="col-md-6">
                        <label class="form-label" for="school_name">Nama Sekolah</label>
                        <input class="form-control" id="school_name" name="school_name" value="{{ old('school_name', $school->name) }}" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label" for="npsn">NPSN</label>
                        <input class="form-control" id="npsn" name="npsn" value="{{ old('npsn', $school->npsn) }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label" for="level">Jenjang</label>
                        <select class="form-select" id="level" name="level" required>
                            @foreach (['PAUD', 'TK', 'SD', 'SMP', 'SMA', 'SMK', 'MA', 'PKBM'] as $level)
                                <option value="{{ $level }}" @selected(old('level', $school->level) === $level)>{{ $level }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label" for="address">Alamat</label>
                        <textarea class="form-control" id="address" name="address" rows="3">{{ old('address', $school->address) }}</textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="phone">Telepon</label>
                        <input class="form-control" id="phone" name="phone" value="{{ old('phone', $school->phone) }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="email">Email Sekolah</label>
                        <input type="email" class="form-control" id="email" name="email" value="{{ old('email', $school->email) }}">
                    </div>
                    <div class="col-12">
                        <div class="border rounded p-3">
                            <div class="d-flex align-items-center gap-2 mb-3">
                                <span class="avatar avatar-sm rounded bg-label-primary">
                                    <i class="bx bx-time-five"></i>
                                </span>
                                <div>
                                    <h6 class="mb-0">Pengaturan Presensi Harian</h6>
                                    <div class="text-muted small">Dipakai untuk scan datang, pulang, dan deteksi keterlambatan.</div>
                                </div>
                            </div>
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label" for="daily_check_in_time">Jam Masuk</label>
                                    <input type="time" class="form-control" id="daily_check_in_time" name="daily_check_in_time" value="{{ old('daily_check_in_time', substr($school->daily_check_in_time ?? '07:00:00', 0, 5)) }}" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label" for="daily_late_tolerance_minutes">Toleransi Terlambat</label>
                                    <div class="input-group">
                                        <input type="number" min="0" max="240" class="form-control" id="daily_late_tolerance_minutes" name="daily_late_tolerance_minutes" value="{{ old('daily_late_tolerance_minutes', $school->daily_late_tolerance_minutes ?? 10) }}" required>
                                        <span class="input-group-text">menit</span>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label" for="daily_check_out_time">Jam Pulang</label>
                                    <input type="time" class="form-control" id="daily_check_out_time" name="daily_check_out_time" value="{{ old('daily_check_out_time', substr($school->daily_check_out_time ?? '14:00:00', 0, 5)) }}" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label" for="daily_early_leave_tolerance_minutes">Toleransi Pulang Cepat</label>
                                    <div class="input-group">
                                        <input type="number" min="0" max="240" class="form-control" id="daily_early_leave_tolerance_minutes" name="daily_early_leave_tolerance_minutes" value="{{ old('daily_early_leave_tolerance_minutes', $school->daily_early_leave_tolerance_minutes ?? 0) }}" required>
                                        <span class="input-group-text">menit</span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label" for="daily_min_checkout_minutes">Minimal Jarak Scan Pulang</label>
                                    <div class="input-group">
                                        <input type="number" min="0" max="720" class="form-control" id="daily_min_checkout_minutes" name="daily_min_checkout_minutes" value="{{ old('daily_min_checkout_minutes', $school->daily_min_checkout_minutes ?? 60) }}" required>
                                        <span class="input-group-text">menit</span>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Hari Sekolah</label>
                                    <div class="d-flex flex-wrap gap-2">
                                        @foreach($schoolDayLabels as $dayNumber => $dayLabel)
                                            <input class="btn-check" type="checkbox" id="school_attendance_day_{{ $dayNumber }}" name="school_attendance_days[]" value="{{ $dayNumber }}" @checked(in_array($dayNumber, $schoolAttendanceDays, true))>
                                            <label class="btn btn-label-primary" for="school_attendance_day_{{ $dayNumber }}">
                                                {{ $dayLabel }}
                                            </label>
                                        @endforeach
                                    </div>
                                    <div class="form-text">Tanggal di luar hari sekolah tidak dihitung sebagai hari presensi. Default umum: Senin sampai Sabtu.</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="border rounded p-3">
                            <div class="d-flex align-items-center gap-2 mb-3">
                                <span class="avatar avatar-sm rounded bg-label-success">
                                    <i class="bx bx-current-location"></i>
                                </span>
                                <div>
                                    <h6 class="mb-0">Pengaturan Presensi Guru</h6>
                                    <div class="text-muted small">Dipakai untuk selfie, geolocation, radius sekolah, dan deteksi keterlambatan guru.</div>
                                </div>
                            </div>
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <label class="form-label" for="teacher_check_in_time">Jam Masuk Guru</label>
                                    <input type="time" class="form-control" id="teacher_check_in_time" name="teacher_check_in_time" value="{{ old('teacher_check_in_time', substr($school->teacher_check_in_time ?? '07:00:00', 0, 5)) }}" required>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label" for="teacher_late_tolerance_minutes">Toleransi Telat</label>
                                    <div class="input-group">
                                        <input type="number" min="0" max="240" class="form-control" id="teacher_late_tolerance_minutes" name="teacher_late_tolerance_minutes" value="{{ old('teacher_late_tolerance_minutes', $school->teacher_late_tolerance_minutes ?? 10) }}" required>
                                        <span class="input-group-text">menit</span>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label" for="teacher_check_out_time">Jam Pulang Guru</label>
                                    <input type="time" class="form-control" id="teacher_check_out_time" name="teacher_check_out_time" value="{{ old('teacher_check_out_time', substr($school->teacher_check_out_time ?? '14:00:00', 0, 5)) }}" required>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label" for="teacher_early_leave_tolerance_minutes">Toleransi Pulang Cepat</label>
                                    <div class="input-group">
                                        <input type="number" min="0" max="240" class="form-control" id="teacher_early_leave_tolerance_minutes" name="teacher_early_leave_tolerance_minutes" value="{{ old('teacher_early_leave_tolerance_minutes', $school->teacher_early_leave_tolerance_minutes ?? 0) }}" required>
                                        <span class="input-group-text">menit</span>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label" for="teacher_attendance_latitude">Latitude Sekolah</label>
                                    <input type="number" step="any" class="form-control" id="teacher_attendance_latitude" name="teacher_attendance_latitude" value="{{ $teacherAttendanceLatitude }}" placeholder="-6.123456">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label" for="teacher_attendance_longitude">Longitude Sekolah</label>
                                    <input type="number" step="any" class="form-control" id="teacher_attendance_longitude" name="teacher_attendance_longitude" value="{{ $teacherAttendanceLongitude }}" placeholder="106.123456">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label" for="teacher_attendance_radius_meters">Radius Presensi</label>
                                    <div class="input-group">
                                        <input type="number" min="1" max="5000" class="form-control" id="teacher_attendance_radius_meters" name="teacher_attendance_radius_meters" value="{{ $teacherAttendanceRadius }}" required>
                                        <span class="input-group-text">meter</span>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label" for="teacher_attendance_max_accuracy_meters">Maks. Akurasi GPS</label>
                                    <div class="input-group">
                                        <input type="number" min="1" max="5000" class="form-control" id="teacher_attendance_max_accuracy_meters" name="teacher_attendance_max_accuracy_meters" value="{{ old('teacher_attendance_max_accuracy_meters', $school->teacher_attendance_max_accuracy_meters ?? 200) }}" required>
                                        <span class="input-group-text">meter</span>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-2">
                                        <label class="form-label mb-0" for="schoolLocationMap">Titik Lokasi Sekolah</label>
                                        <div class="d-flex flex-wrap gap-2">
                                            <button type="button" class="btn btn-sm btn-label-primary" id="useCurrentLocationButton">
                                                <i class="bx bx-current-location me-1"></i>
                                                Gunakan Lokasi Saya
                                            </button>
                                            <button type="button" class="btn btn-sm btn-label-secondary" id="focusSchoolLocationButton">
                                                <i class="bx bx-target-lock me-1"></i>
                                                Fokus Marker
                                            </button>
                                        </div>
                                    </div>
                                    <div
                                        id="schoolLocationMap"
                                        class="school-location-map"
                                        data-latitude="{{ $teacherAttendanceLatitude }}"
                                        data-longitude="{{ $teacherAttendanceLongitude }}"
                                        data-radius="{{ $teacherAttendanceRadius }}"
                                        aria-label="Peta lokasi sekolah"
                                    ></div>
                                    <div class="form-text school-location-map-status" id="schoolLocationMapStatus">
                                        Klik peta atau geser marker untuk mengisi latitude dan longitude.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12">
                        <label class="form-label" for="logo">Logo Sekolah</label>
                        <div class="d-flex flex-wrap align-items-center gap-4">
                            <div class="border rounded d-flex align-items-center justify-content-center bg-light" style="width: 104px; height: 104px;">
                                @if($school->logo_path)
                                    <img id="logoPreview" src="{{ \App\Support\SchoolFileStorage::url($school->logo_path) }}" alt="Logo sekolah" class="img-fluid rounded" style="max-width: 96px; max-height: 96px;">
                                @else
                                    <img id="logoPreview" src="" alt="Preview logo sekolah" class="img-fluid rounded d-none" style="max-width: 96px; max-height: 96px;">
                                    <i id="logoPlaceholder" class="bx bx-image text-muted fs-1"></i>
                                @endif
                            </div>
                            <div class="flex-grow-1">
                                <input type="file" class="form-control" id="logo" name="logo" accept="image/png,image/jpeg" onchange="previewSchoolLogo(event)">
                                <div class="form-text">Format JPG atau PNG. Ukuran maksimal 2MB.</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12">
                        <label class="form-label" for="nametag_background">Background Nametag</label>
                        <div class="d-flex flex-wrap align-items-center gap-4">
                            <div class="border rounded d-flex align-items-center justify-content-center bg-light overflow-hidden" style="width: 108px; height: 172px;">
                                @if($school->nametag_background_path)
                                    <img id="nametagBackgroundPreview" src="{{ \App\Support\SchoolFileStorage::url($school->nametag_background_path) }}" alt="Background nametag" class="img-fluid" style="width: 100%; height: 100%; object-fit: cover;">
                                @else
                                    <img id="nametagBackgroundPreview" src="" alt="Preview background nametag" class="img-fluid d-none" style="width: 100%; height: 100%; object-fit: cover;">
                                    <i id="nametagBackgroundPlaceholder" class="bx bx-id-card text-muted fs-1"></i>
                                @endif
                            </div>
                            <div class="flex-grow-1">
                                <input type="file" class="form-control" id="nametag_background" name="nametag_background" accept="image/png,image/jpeg,image/webp" onchange="previewNametagBackground(event)">
                                <div class="form-text">Format JPG, PNG, atau WebP. Disarankan portrait ukuran ID card 54 x 86 mm. Maksimal 5MB.</div>
                                @if($school->nametag_background_path)
                                    <div class="form-check mt-3">
                                        <input class="form-check-input" type="checkbox" id="remove_nametag_background" name="remove_nametag_background" value="1">
                                        <label class="form-check-label text-danger" for="remove_nametag_background">
                                            Hapus background nametag saat disimpan
                                        </label>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-primary me-2">Simpan Perubahan</button>
                    <a href="{{ route('dashboard') }}" class="btn btn-label-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="{{ asset('assets/vendor/libs/leaflet/leaflet.js') }}"></script>
<script>
function previewSchoolLogo(event) {
    const file = event.target.files[0];
    const preview = document.getElementById('logoPreview');
    const placeholder = document.getElementById('logoPlaceholder');

    if (!file || !preview) {
        return;
    }

    preview.src = URL.createObjectURL(file);
    preview.classList.remove('d-none');

    if (placeholder) {
        placeholder.classList.add('d-none');
    }
}

function previewNametagBackground(event) {
    const file = event.target.files[0];
    const preview = document.getElementById('nametagBackgroundPreview');
    const placeholder = document.getElementById('nametagBackgroundPlaceholder');

    if (!file || !preview) {
        return;
    }

    preview.src = URL.createObjectURL(file);
    preview.classList.remove('d-none');

    if (placeholder) {
        placeholder.classList.add('d-none');
    }
}

document.addEventListener('DOMContentLoaded', function () {
    const mapElement = document.getElementById('schoolLocationMap');
    const latitudeInput = document.getElementById('teacher_attendance_latitude');
    const longitudeInput = document.getElementById('teacher_attendance_longitude');
    const radiusInput = document.getElementById('teacher_attendance_radius_meters');
    const statusElement = document.getElementById('schoolLocationMapStatus');
    const useCurrentLocationButton = document.getElementById('useCurrentLocationButton');
    const focusSchoolLocationButton = document.getElementById('focusSchoolLocationButton');

    if (!mapElement || !latitudeInput || !longitudeInput || typeof L === 'undefined') {
        return;
    }

    const defaultLocation = [-2.5489, 118.0149];
    const defaultZoom = 5;
    const selectedZoom = 17;

    function parseCoordinate(value, min, max) {
        const number = Number.parseFloat(value);

        if (Number.isNaN(number) || number < min || number > max) {
            return null;
        }

        return number;
    }

    function getInputLocation() {
        const latitude = parseCoordinate(latitudeInput.value, -90, 90);
        const longitude = parseCoordinate(longitudeInput.value, -180, 180);

        if (latitude === null || longitude === null) {
            return null;
        }

        return [latitude, longitude];
    }

    function getRadius() {
        const radius = Number.parseInt(radiusInput?.value, 10);

        if (Number.isNaN(radius) || radius < 1) {
            return 150;
        }

        return radius;
    }

    function setStatus(message, tone = 'muted') {
        if (!statusElement) {
            return;
        }

        statusElement.className = 'form-text school-location-map-status text-' + tone;
        statusElement.textContent = message;
    }

    const initialLocation = getInputLocation();
    const map = L.map(mapElement).setView(initialLocation || defaultLocation, initialLocation ? selectedZoom : defaultZoom);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);

    const marker = L.marker(initialLocation || defaultLocation, {
        draggable: true,
        opacity: initialLocation ? 1 : .65
    }).addTo(map);

    const radiusCircle = L.circle(initialLocation || defaultLocation, {
        radius: getRadius(),
        color: '#71dd37',
        fillColor: '#71dd37',
        fillOpacity: .12,
        weight: 2,
        interactive: false
    }).addTo(map);

    function syncLocation(location, shouldPan = true) {
        const latitude = Number.parseFloat(location[0]).toFixed(7);
        const longitude = Number.parseFloat(location[1]).toFixed(7);
        const latLng = [Number.parseFloat(latitude), Number.parseFloat(longitude)];

        latitudeInput.value = latitude;
        longitudeInput.value = longitude;
        marker.setLatLng(latLng);
        marker.setOpacity(1);
        radiusCircle.setLatLng(latLng);

        if (shouldPan) {
            map.setView(latLng, Math.max(map.getZoom(), selectedZoom));
        }

        setStatus('Koordinat dipilih: ' + latitude + ', ' + longitude + '.', 'success');
    }

    function syncFromInputs() {
        const location = getInputLocation();

        if (!location) {
            setStatus('Isi latitude dan longitude yang valid, atau pilih titik dari peta.', 'muted');
            return;
        }

        marker.setLatLng(location);
        marker.setOpacity(1);
        radiusCircle.setLatLng(location);
        map.setView(location, Math.max(map.getZoom(), selectedZoom));
        setStatus('Marker mengikuti koordinat yang diisi.', 'success');
    }

    map.on('click', function (event) {
        syncLocation([event.latlng.lat, event.latlng.lng], false);
    });

    marker.on('dragend', function (event) {
        const location = event.target.getLatLng();
        syncLocation([location.lat, location.lng], false);
    });

    latitudeInput.addEventListener('change', syncFromInputs);
    longitudeInput.addEventListener('change', syncFromInputs);

    radiusInput?.addEventListener('input', function () {
        radiusCircle.setRadius(getRadius());
    });

    useCurrentLocationButton?.addEventListener('click', function () {
        if (!navigator.geolocation) {
            setStatus('Browser tidak mendukung geolocation.', 'danger');
            return;
        }

        useCurrentLocationButton.disabled = true;
        setStatus('Mengambil lokasi perangkat...', 'primary');

        navigator.geolocation.getCurrentPosition(
            function (position) {
                syncLocation([position.coords.latitude, position.coords.longitude]);
                setStatus('Lokasi perangkat digunakan sebagai titik sekolah.', 'success');
                useCurrentLocationButton.disabled = false;
            },
            function () {
                setStatus('Lokasi perangkat tidak bisa diambil. Pastikan izin lokasi browser aktif.', 'danger');
                useCurrentLocationButton.disabled = false;
            },
            {
                enableHighAccuracy: true,
                timeout: 10000,
                maximumAge: 0
            }
        );
    });

    focusSchoolLocationButton?.addEventListener('click', function () {
        const location = getInputLocation();

        if (!location) {
            setStatus('Belum ada koordinat sekolah untuk difokuskan.', 'warning');
            return;
        }

        map.setView(location, selectedZoom);
        marker.setLatLng(location);
        marker.setOpacity(1);
        radiusCircle.setLatLng(location);
        setStatus('Peta difokuskan ke titik sekolah.', 'success');
    });

    if (initialLocation) {
        setStatus('Koordinat sekolah saat ini ditampilkan di peta.', 'success');
    }

    setTimeout(function () {
        map.invalidateSize();
    }, 250);
});
</script>
@endsection
