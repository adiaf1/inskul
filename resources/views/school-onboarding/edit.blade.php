@extends('layouts.app')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    @if($errors->any())
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                Swal.fire({
                    title: 'Error!',
                    text: '{{ $errors->first() }}',
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
            });
        </script>
    @endif

    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 py-4 mb-2">
        <div>
            <h4 class="mb-1">Onboarding Sekolah</h4>
            <p class="text-muted mb-0">Lengkapi data dasar sebelum mulai memakai dashboard sekolah.</p>
        </div>
        <span class="badge bg-label-warning">Wajib diselesaikan</span>
    </div>

    <form method="POST" action="{{ route('school-onboarding.update') }}" enctype="multipart/form-data">
        @csrf

        <div class="row g-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">1. Profil Sekolah</h5>
                    </div>
                    <div class="card-body">
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
                                <textarea class="form-control" id="address" name="address" rows="2">{{ old('address', $school->address) }}</textarea>
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
                                <label class="form-label" for="logo">Logo Sekolah</label>
                                <div class="d-flex flex-wrap align-items-center gap-4">
                                    <div class="border rounded d-flex align-items-center justify-content-center bg-light" style="width: 96px; height: 96px;">
                                        @if($school->logo_path)
                                            <img id="logoPreview" src="{{ asset($school->logo_path) }}" alt="Logo sekolah" class="img-fluid rounded" style="max-width: 88px; max-height: 88px;">
                                        @else
                                            <img id="logoPreview" src="" alt="Preview logo sekolah" class="img-fluid rounded d-none" style="max-width: 88px; max-height: 88px;">
                                            <i id="logoPlaceholder" class="bx bx-image text-muted fs-1"></i>
                                        @endif
                                    </div>
                                    <div class="flex-grow-1">
                                        <input type="file" class="form-control" id="logo" name="logo" accept="image/png,image/jpeg" onchange="previewSchoolLogo(event)">
                                        <div class="form-text">Format JPG atau PNG. Ukuran maksimal 2MB.</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">2. Tahun Ajaran & Semester Aktif</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-4">
                            <div class="col-md-4">
                                <label class="form-label" for="academic_year_name">Nama Tahun Ajaran</label>
                                <input class="form-control" id="academic_year_name" name="academic_year_name" value="{{ old('academic_year_name', '2025/2026') }}" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label" for="academic_year_starts_at">Mulai Tahun Ajaran</label>
                                <input type="date" class="form-control" id="academic_year_starts_at" name="academic_year_starts_at" value="{{ old('academic_year_starts_at') }}" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label" for="academic_year_ends_at">Selesai Tahun Ajaran</label>
                                <input type="date" class="form-control" id="academic_year_ends_at" name="academic_year_ends_at" value="{{ old('academic_year_ends_at') }}" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label" for="semester_name">Semester Aktif</label>
                                <select class="form-select" id="semester_name" name="semester_name" required>
                                    <option value="Ganjil" @selected(old('semester_name') === 'Ganjil')>Ganjil</option>
                                    <option value="Genap" @selected(old('semester_name') === 'Genap')>Genap</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label" for="semester_starts_at">Mulai Semester</label>
                                <input type="date" class="form-control" id="semester_starts_at" name="semester_starts_at" value="{{ old('semester_starts_at') }}" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label" for="semester_ends_at">Selesai Semester</label>
                                <input type="date" class="form-control" id="semester_ends_at" name="semester_ends_at" value="{{ old('semester_ends_at') }}" required>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card h-100">
                    <div class="card-header">
                        <h5 class="mb-0">3. Kelas Awal</h5>
                    </div>
                    <div class="card-body">
                        <label class="form-label" for="classes">Daftar Kelas</label>
                        <textarea class="form-control" id="classes" name="classes" rows="8" required placeholder="X RPL 1 | X&#10;X RPL 2 | X&#10;XI TKJ 1 | XI">{{ old('classes') }}</textarea>
                        <div class="form-text">Satu baris satu kelas. Format: nama kelas | tingkat.</div>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card h-100">
                    <div class="card-header">
                        <h5 class="mb-0">4. Mata Pelajaran Awal</h5>
                    </div>
                    <div class="card-body">
                        <label class="form-label" for="subjects">Daftar Mata Pelajaran</label>
                        <textarea class="form-control" id="subjects" name="subjects" rows="8" required placeholder="Matematika | MTK&#10;Bahasa Indonesia | BIN&#10;Bahasa Inggris | BIG">{{ old('subjects') }}</textarea>
                        <div class="form-text">Satu baris satu mata pelajaran. Format: nama mapel | kode.</div>
                    </div>
                </div>
            </div>

            <div class="col-12">
                <div class="card">
                    <div class="card-body d-flex flex-wrap justify-content-between gap-3">
                        <div>
                            <h6 class="mb-1">Selesaikan onboarding</h6>
                            <p class="text-muted mb-0">Setelah disimpan, Admin Sekolah akan masuk ke dashboard utama.</p>
                        </div>
                        <button type="submit" class="btn btn-primary">Selesaikan Onboarding</button>
                    </div>
                </div>
            </div>
        </div>
    </form>
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
</script>
@endsection
