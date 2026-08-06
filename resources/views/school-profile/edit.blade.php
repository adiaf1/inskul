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
</script>
@endsection
