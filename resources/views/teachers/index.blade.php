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
            <h4 class="mb-1">Guru</h4>
            <p class="text-muted mb-0">{{ $school->name }} - kelola profil guru dan akun login guru.</p>
        </div>

        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('teachers.import-template') }}" class="btn btn-label-primary">
                <i class="bx bx-download me-1"></i> Download Format
            </a>
            <button class="btn btn-label-secondary" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasImportTeacher">
                <i class="bx bx-upload me-1"></i> Import Guru
            </button>
            <button class="btn btn-primary" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasAddTeacher">
                <i class="bx bx-user-plus me-1"></i> Tambah Guru
            </button>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <form method="GET" action="{{ route('teachers.index') }}" class="mb-4">
                <div class="d-flex flex-column flex-md-row gap-3">
                    <select name="status" class="form-select" onchange="this.form.submit()">
                        <option value="">Semua Status</option>
                        <option value="active" @selected($status === 'active')>Aktif</option>
                        <option value="inactive" @selected($status === 'inactive')>Tidak Aktif</option>
                    </select>
                    <div class="input-group">
                        <input type="search" name="search" class="form-control" value="{{ request('search') }}" placeholder="Cari nama, email, NIP, NUPTK">
                        <button class="btn btn-outline-primary" type="submit">Cari</button>
                    </div>
                </div>
            </form>

            <div class="table-responsive text-nowrap">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Guru</th>
                            <th>NIP/NUPTK</th>
                            <th>Kontak</th>
                            <th>Status</th>
                            <th class="text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($teachers as $teacher)
                            <tr>
                                <td>
                                    <strong>{{ $teacher->user?->name }}</strong>
                                    <div class="text-muted small">{{ $teacher->user?->email }}</div>
                                </td>
                                <td>
                                    <div>{{ $teacher->nip ?: '-' }}</div>
                                    <div class="text-muted small">{{ $teacher->nuptk ? 'NUPTK: '.$teacher->nuptk : '' }}</div>
                                </td>
                                <td>
                                    <div>{{ $teacher->phone ?: '-' }}</div>
                                    <div class="text-muted small">
                                        {{ $teacher->gender === 'male' ? 'Laki-laki' : ($teacher->gender === 'female' ? 'Perempuan' : '-') }}
                                    </div>
                                </td>
                                <td>
                                    @if($teacher->is_active)
                                        <span class="badge bg-label-success">Aktif</span>
                                    @else
                                        <span class="badge bg-label-secondary">Tidak Aktif</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <button type="button" class="btn btn-sm btn-label-primary" data-bs-toggle="offcanvas" data-bs-target="#offcanvasEditTeacher{{ $teacher->id }}">
                                        Edit
                                    </button>
                                    @if($teacher->is_active)
                                        <form method="POST" action="{{ route('teachers.destroy', $teacher) }}" class="d-inline" id="deactivate-teacher-{{ $teacher->id }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="confirmDeactivateTeacher({{ $teacher->id }})">
                                                Nonaktifkan
                                            </button>
                                        </form>
                                    @endif
                                </td>
                            </tr>

                            <div class="offcanvas offcanvas-start" tabindex="-1" id="offcanvasEditTeacher{{ $teacher->id }}" aria-labelledby="offcanvasEditTeacherLabel{{ $teacher->id }}">
                                <div class="offcanvas-header border-bottom">
                                    <h5 id="offcanvasEditTeacherLabel{{ $teacher->id }}" class="offcanvas-title">Edit Guru</h5>
                                    <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                                </div>
                                <div class="offcanvas-body mx-0 flex-grow-0 p-6 h-100">
                                    <form method="POST" action="{{ route('teachers.update', $teacher) }}">
                                        @csrf
                                        @method('PUT')

                                        <h6 class="mb-3">Akun Login</h6>
                                        <div class="mb-4">
                                            <label class="form-label" for="edit_name_{{ $teacher->id }}">Nama Guru</label>
                                            <input class="form-control" id="edit_name_{{ $teacher->id }}" name="name" value="{{ old('name', $teacher->user?->name) }}" required>
                                        </div>
                                        <div class="mb-4">
                                            <label class="form-label" for="edit_email_{{ $teacher->id }}">Email Login</label>
                                            <input type="email" class="form-control" id="edit_email_{{ $teacher->id }}" name="email" value="{{ old('email', $teacher->user?->email) }}" required>
                                        </div>

                                        <h6 class="mb-3 mt-2">Profil Guru</h6>
                                        <div class="mb-4">
                                            <label class="form-label" for="edit_nip_{{ $teacher->id }}">NIP</label>
                                            <input class="form-control" id="edit_nip_{{ $teacher->id }}" name="nip" value="{{ old('nip', $teacher->nip) }}">
                                        </div>
                                        <div class="mb-4">
                                            <label class="form-label" for="edit_nuptk_{{ $teacher->id }}">NUPTK</label>
                                            <input class="form-control" id="edit_nuptk_{{ $teacher->id }}" name="nuptk" value="{{ old('nuptk', $teacher->nuptk) }}">
                                        </div>
                                        <div class="mb-4">
                                            <label class="form-label" for="edit_employee_number_{{ $teacher->id }}">Nomor Pegawai</label>
                                            <input class="form-control" id="edit_employee_number_{{ $teacher->id }}" name="employee_number" value="{{ old('employee_number', $teacher->employee_number) }}">
                                        </div>
                                        <div class="mb-4">
                                            <label class="form-label" for="edit_gender_{{ $teacher->id }}">Jenis Kelamin</label>
                                            <select class="form-select" id="edit_gender_{{ $teacher->id }}" name="gender">
                                                <option value="">Pilih jenis kelamin</option>
                                                <option value="male" @selected(old('gender', $teacher->gender) === 'male')>Laki-laki</option>
                                                <option value="female" @selected(old('gender', $teacher->gender) === 'female')>Perempuan</option>
                                            </select>
                                        </div>
                                        <div class="mb-4">
                                            <label class="form-label" for="edit_phone_{{ $teacher->id }}">Telepon</label>
                                            <input class="form-control" id="edit_phone_{{ $teacher->id }}" name="phone" value="{{ old('phone', $teacher->phone) }}">
                                        </div>
                                        <div class="mb-4">
                                            <label class="form-label" for="edit_birth_place_{{ $teacher->id }}">Tempat Lahir</label>
                                            <input class="form-control" id="edit_birth_place_{{ $teacher->id }}" name="birth_place" value="{{ old('birth_place', $teacher->birth_place) }}">
                                        </div>
                                        <div class="mb-4">
                                            <label class="form-label" for="edit_birth_date_{{ $teacher->id }}">Tanggal Lahir</label>
                                            <input type="date" class="form-control" id="edit_birth_date_{{ $teacher->id }}" name="birth_date" value="{{ old('birth_date', $teacher->birth_date?->format('Y-m-d')) }}">
                                        </div>
                                        <div class="mb-4">
                                            <label class="form-label" for="edit_address_{{ $teacher->id }}">Alamat</label>
                                            <textarea class="form-control" id="edit_address_{{ $teacher->id }}" name="address" rows="3">{{ old('address', $teacher->address) }}</textarea>
                                        </div>
                                        <div class="form-check mb-4">
                                            <input class="form-check-input" type="checkbox" id="edit_is_active_{{ $teacher->id }}" name="is_active" value="1" @checked(old('is_active', $teacher->is_active))>
                                            <label class="form-check-label" for="edit_is_active_{{ $teacher->id }}">Guru aktif</label>
                                        </div>

                                        <button type="submit" class="btn btn-primary me-2">Simpan Perubahan</button>
                                        <button type="button" class="btn btn-label-secondary" data-bs-dismiss="offcanvas">Batal</button>
                                    </form>
                                </div>
                            </div>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-5 text-muted">Belum ada data guru.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($teachers->hasPages())
                <div class="mt-4">{{ $teachers->links() }}</div>
            @endif
        </div>
    </div>

    <div class="offcanvas offcanvas-start" tabindex="-1" id="offcanvasImportTeacher" aria-labelledby="offcanvasImportTeacherLabel">
        <div class="offcanvas-header border-bottom">
            <h5 id="offcanvasImportTeacherLabel" class="offcanvas-title">Import Guru</h5>
            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body mx-0 flex-grow-0 p-6 h-100">
            <div class="alert alert-info">
                Gunakan file dari tombol <strong>Download Format</strong>. Gender diisi <strong>L</strong> atau <strong>P</strong>. Data hasil import otomatis aktif.
            </div>
            <form method="POST" action="{{ route('teachers.import') }}" enctype="multipart/form-data">
                @csrf

                <div class="mb-4">
                    <label class="form-label" for="file">File Import</label>
                    <input type="file" class="form-control" id="file" name="file" accept=".csv,text/csv" required>
                    <div class="form-text">Upload file CSV sesuai format. Maksimal 2MB.</div>
                </div>

                <button type="submit" class="btn btn-primary me-2">Import</button>
                <button type="button" class="btn btn-label-secondary" data-bs-dismiss="offcanvas">Batal</button>
            </form>
        </div>
    </div>

    <div class="offcanvas offcanvas-start" tabindex="-1" id="offcanvasAddTeacher" aria-labelledby="offcanvasAddTeacherLabel">
        <div class="offcanvas-header border-bottom">
            <h5 id="offcanvasAddTeacherLabel" class="offcanvas-title">Tambah Guru</h5>
            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body mx-0 flex-grow-0 p-6 h-100">
            <form method="POST" action="{{ route('teachers.store') }}">
                @csrf

                <h6 class="mb-3">Akun Login</h6>
                <div class="mb-4">
                    <label class="form-label" for="name">Nama Guru</label>
                    <input class="form-control" id="name" name="name" value="{{ old('name') }}" required>
                </div>
                <div class="mb-4">
                    <label class="form-label" for="email">Email Login</label>
                    <input type="email" class="form-control" id="email" name="email" value="{{ old('email') }}" required>
                </div>
                <div class="mb-4">
                    <label class="form-label" for="password">Kata Sandi</label>
                    <input type="password" class="form-control" id="password" name="password" required autocomplete="new-password">
                </div>
                <div class="mb-4">
                    <label class="form-label" for="password_confirmation">Konfirmasi Kata Sandi</label>
                    <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" required autocomplete="new-password">
                </div>

                <h6 class="mb-3 mt-2">Profil Guru</h6>
                <div class="mb-4">
                    <label class="form-label" for="nip">NIP</label>
                    <input class="form-control" id="nip" name="nip" value="{{ old('nip') }}">
                </div>
                <div class="mb-4">
                    <label class="form-label" for="nuptk">NUPTK</label>
                    <input class="form-control" id="nuptk" name="nuptk" value="{{ old('nuptk') }}">
                </div>
                <div class="mb-4">
                    <label class="form-label" for="employee_number">Nomor Pegawai</label>
                    <input class="form-control" id="employee_number" name="employee_number" value="{{ old('employee_number') }}">
                </div>
                <div class="mb-4">
                    <label class="form-label" for="gender">Jenis Kelamin</label>
                    <select class="form-select" id="gender" name="gender">
                        <option value="">Pilih jenis kelamin</option>
                        <option value="male" @selected(old('gender') === 'male')>Laki-laki</option>
                        <option value="female" @selected(old('gender') === 'female')>Perempuan</option>
                    </select>
                </div>
                <div class="mb-4">
                    <label class="form-label" for="phone">Telepon</label>
                    <input class="form-control" id="phone" name="phone" value="{{ old('phone') }}">
                </div>
                <div class="mb-4">
                    <label class="form-label" for="birth_place">Tempat Lahir</label>
                    <input class="form-control" id="birth_place" name="birth_place" value="{{ old('birth_place') }}">
                </div>
                <div class="mb-4">
                    <label class="form-label" for="birth_date">Tanggal Lahir</label>
                    <input type="date" class="form-control" id="birth_date" name="birth_date" value="{{ old('birth_date') }}">
                </div>
                <div class="mb-4">
                    <label class="form-label" for="address">Alamat</label>
                    <textarea class="form-control" id="address" name="address" rows="3">{{ old('address') }}</textarea>
                </div>
                <div class="form-check mb-4">
                    <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" @checked(old('is_active', true))>
                    <label class="form-check-label" for="is_active">Guru aktif</label>
                </div>

                <button type="submit" class="btn btn-primary me-2">Simpan</button>
                <button type="button" class="btn btn-label-secondary" data-bs-dismiss="offcanvas">Batal</button>
            </form>
        </div>
    </div>
</div>

<script>
function confirmDeactivateTeacher(id) {
    Swal.fire({
        title: 'Nonaktifkan guru?',
        text: 'Akun login guru juga akan dinonaktifkan.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Ya, nonaktifkan',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('deactivate-teacher-' + id).submit();
        }
    });
}
</script>
@endsection
