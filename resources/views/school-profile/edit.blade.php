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
</script>
@endsection
