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
            <h4 class="mb-1">Murid</h4>
            <p class="text-muted mb-0">{{ $school->name }} - kelola profil murid dan akun login murid.</p>
        </div>

        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('students.export', request()->only(['search', 'status', 'entry_year'])) }}" class="btn btn-label-success">
                <i class="bx bx-spreadsheet me-1"></i> Export Data
            </a>
            <button type="button" class="btn btn-label-info" id="printSelectedNametags" data-print-selected-url="{{ route('students.nametags', request()->only(['search', 'status', 'entry_year'])) }}" disabled>
                <i class="bx bx-printer me-1"></i> Cetak Nametag
                <span class="badge bg-info ms-1" id="selectedNametagCount">0</span>
            </button>
            <a href="{{ route('students.import-template') }}" class="btn btn-label-primary">
                <i class="bx bx-download me-1"></i> Download Format
            </a>
            <button class="btn btn-label-secondary" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasImportStudent">
                <i class="bx bx-upload me-1"></i> Import Murid
            </button>
            <button class="btn btn-primary" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasAddStudent">
                <i class="bx bx-user-plus me-1"></i> Tambah Murid
            </button>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <form method="GET" action="{{ route('students.index') }}" class="mb-4">
                <div class="d-flex flex-column flex-md-row gap-3">
                    <select name="status" class="form-select" onchange="this.form.submit()">
                        <option value="">Semua Status</option>
                        <option value="active" @selected($status === 'active')>Aktif</option>
                        <option value="inactive" @selected($status === 'inactive')>Tidak Aktif</option>
                    </select>
                    <select name="entry_year" class="form-select" onchange="this.form.submit()">
                        <option value="">Semua Angkatan</option>
                        @foreach($entryYears as $year)
                            <option value="{{ $year }}" @selected((string) $entryYear === (string) $year)>Angkatan {{ $year }}</option>
                        @endforeach
                    </select>
                    <div class="input-group">
                        <input type="search" name="search" class="form-control" value="{{ request('search') }}" placeholder="Cari nama, username, email, NIS, NISN">
                        <button class="btn btn-outline-primary" type="submit">Cari</button>
                    </div>
                </div>
            </form>

            <div class="table-responsive text-nowrap">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th style="width: 42px;">
                                <input class="form-check-input" type="checkbox" id="selectAllNametags" title="Pilih semua murid di halaman ini" aria-label="Pilih semua murid di halaman ini">
                            </th>
                            <th>Nama</th>
                            <th>Tanggal Lahir</th>
                            <th>Tahun Angkatan</th>
                            <th>Alamat</th>
                            <th>Status</th>
                            <th class="text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($students as $student)
                            <tr>
                                <td>
                                    <input class="form-check-input" type="checkbox" value="{{ $student->id }}" data-student-nametag-checkbox aria-label="Pilih {{ $student->user?->name }} untuk cetak nametag">
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="avatar">
                                            @if($student->photo_path)
                                                <img src="{{ \App\Support\SchoolFileStorage::url($student->photo_path) }}" alt="Foto {{ $student->user?->name }}" class="rounded object-fit-cover" style="width: 40px; height: 40px;">
                                            @else
                                                <span class="avatar-initial rounded bg-label-secondary">
                                                    {{ strtoupper(substr($student->user?->name ?? 'M', 0, 1)) }}
                                                </span>
                                            @endif
                                        </div>
                                        <div>
                                            <strong>{{ $student->user?->name }}</strong>
                                            <div class="text-muted small">Email : {{ $student->user?->email ?: '-' }}</div>
                                            <div class="text-muted small">Username : {{ $student->user?->username ?: '-' }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div>{{ $student->birth_date?->format('d-m-Y') ?: '-' }}</div>
                                    <div class="text-muted small">
                                        {{ $student->gender === 'male' ? 'Laki-laki' : ($student->gender === 'female' ? 'Perempuan' : '-') }}
                                    </div>
                                </td>
                                <td>
                                    <strong>{{ $student->entry_year ?: '-' }}</strong>
                                    <div class="text-muted small">NIS : {{ $student->nis ?: '-' }}</div>
                                    <div class="text-muted small">NISN : {{ $student->nisn ?: '-' }}</div>
                                </td>
                                <td>
                                    <div>No HP : {{ $student->phone ?: '-' }}</div>
                                    <div class="text-muted small">{{ $student->address ?: '-' }}</div>
                                </td>
                                <td>
                                    @if($student->is_active)
                                        <span class="badge bg-label-success">Aktif</span>
                                    @else
                                        <span class="badge bg-label-secondary">Tidak Aktif</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <a href="{{ route('students.nametag', $student) }}" class="btn btn-sm btn-icon btn-label-info" target="_blank" title="Cetak Nametag" aria-label="Cetak Nametag">
                                        <i class="bx bx-id-card"></i>
                                    </a>
                                    <button type="button" class="btn btn-sm btn-icon btn-label-primary" data-bs-toggle="offcanvas" data-bs-target="#offcanvasEditStudent{{ $student->id }}" title="Edit" aria-label="Edit">
                                        <i class="bx bx-edit-alt"></i>
                                    </button>
                                    @if($student->is_active)
                                        <form method="POST" action="{{ route('students.destroy', $student) }}" class="d-inline" id="deactivate-student-{{ $student->id }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button" class="btn btn-sm btn-icon btn-outline-danger" onclick="confirmDeactivateStudent(@js($student->id))" title="Nonaktifkan" aria-label="Nonaktifkan">
                                                <i class="bx bx-power-off"></i>
                                            </button>
                                        </form>
                                    @endif
                                </td>
                            </tr>

                            <div class="offcanvas offcanvas-start" tabindex="-1" id="offcanvasEditStudent{{ $student->id }}" aria-labelledby="offcanvasEditStudentLabel{{ $student->id }}">
                                <div class="offcanvas-header border-bottom">
                                    <h5 id="offcanvasEditStudentLabel{{ $student->id }}" class="offcanvas-title">Edit Murid</h5>
                                    <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                                </div>
                                <div class="offcanvas-body mx-0 flex-grow-0 p-6 h-100">
                                    <form method="POST" action="{{ route('students.update', $student) }}" enctype="multipart/form-data">
                                        @csrf
                                        @method('PUT')

                                        <h6 class="mb-3">Akun Login</h6>
                                        <div class="mb-4">
                                            <label class="form-label" for="edit_name_{{ $student->id }}">Nama Murid</label>
                                            <input class="form-control" id="edit_name_{{ $student->id }}" name="name" value="{{ old('name', $student->user?->name) }}" required>
                                        </div>
                                        <div class="mb-4">
                                            <label class="form-label" for="edit_email_{{ $student->id }}">Email Login</label>
                                            <input type="email" class="form-control" id="edit_email_{{ $student->id }}" name="email" value="{{ old('email', $student->user?->email) }}" required>
                                        </div>

                                        <h6 class="mb-3 mt-2">Profil Murid</h6>
                                        <div class="mb-4">
                                            <label class="form-label" for="edit_photo_{{ $student->id }}">Foto Murid</label>
                                            @if($student->photo_path)
                                                <div class="mb-2">
                                                    <img src="{{ \App\Support\SchoolFileStorage::url($student->photo_path) }}" alt="Foto {{ $student->user?->name }}" class="rounded object-fit-cover border" style="width: 72px; height: 72px;">
                                                </div>
                                            @endif
                                            <input type="file" class="form-control" id="edit_photo_{{ $student->id }}" name="photo" accept="image/jpeg,image/png,image/webp">
                                            <div class="form-text">Kosongkan jika tidak ingin mengganti foto. JPG, PNG, atau WebP maksimal 2MB.</div>
                                        </div>
                                        <div class="mb-4">
                                            <label class="form-label" for="edit_nis_{{ $student->id }}">NIS</label>
                                            <input class="form-control" id="edit_nis_{{ $student->id }}" name="nis" value="{{ old('nis', $student->nis) }}">
                                        </div>
                                        <div class="mb-4">
                                            <label class="form-label" for="edit_nisn_{{ $student->id }}">NISN</label>
                                            <input class="form-control" id="edit_nisn_{{ $student->id }}" name="nisn" value="{{ old('nisn', $student->nisn) }}">
                                        </div>
                                        <div class="mb-4">
                                            <label class="form-label" for="edit_entry_year_{{ $student->id }}">Angkatan</label>
                                            <input type="number" min="1900" max="2100" class="form-control" id="edit_entry_year_{{ $student->id }}" name="entry_year" value="{{ old('entry_year', $student->entry_year) }}" placeholder="Contoh: 2026">
                                        </div>
                                        <div class="mb-4">
                                            <label class="form-label" for="edit_gender_{{ $student->id }}">Jenis Kelamin</label>
                                            <select class="form-select" id="edit_gender_{{ $student->id }}" name="gender">
                                                <option value="">Pilih jenis kelamin</option>
                                                <option value="male" @selected(old('gender', $student->gender) === 'male')>Laki-laki</option>
                                                <option value="female" @selected(old('gender', $student->gender) === 'female')>Perempuan</option>
                                            </select>
                                        </div>
                                        <div class="mb-4">
                                            <label class="form-label" for="edit_phone_{{ $student->id }}">Telepon</label>
                                            <input class="form-control" id="edit_phone_{{ $student->id }}" name="phone" value="{{ old('phone', $student->phone) }}">
                                        </div>
                                        <div class="mb-4">
                                            <label class="form-label" for="edit_birth_place_{{ $student->id }}">Tempat Lahir</label>
                                            <input class="form-control" id="edit_birth_place_{{ $student->id }}" name="birth_place" value="{{ old('birth_place', $student->birth_place) }}">
                                        </div>
                                        <div class="mb-4">
                                            <label class="form-label" for="edit_birth_date_{{ $student->id }}">Tanggal Lahir</label>
                                            <input type="date" class="form-control" id="edit_birth_date_{{ $student->id }}" name="birth_date" value="{{ old('birth_date', $student->birth_date?->format('Y-m-d')) }}">
                                        </div>
                                        <div class="mb-4">
                                            <label class="form-label" for="edit_address_{{ $student->id }}">Alamat</label>
                                            <textarea class="form-control" id="edit_address_{{ $student->id }}" name="address" rows="3">{{ old('address', $student->address) }}</textarea>
                                        </div>
                                        <div class="form-check mb-4">
                                            <input class="form-check-input" type="checkbox" id="edit_is_active_{{ $student->id }}" name="is_active" value="1" @checked(old('is_active', $student->is_active))>
                                            <label class="form-check-label" for="edit_is_active_{{ $student->id }}">Murid aktif</label>
                                        </div>

                                        <button type="submit" class="btn btn-primary me-2">Simpan Perubahan</button>
                                        <button type="button" class="btn btn-label-secondary" data-bs-dismiss="offcanvas">Batal</button>
                                    </form>
                                </div>
                            </div>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">Belum ada data murid.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <x-table-pagination :paginator="$students" label="murid" />
        </div>
    </div>

    <div class="offcanvas offcanvas-start" tabindex="-1" id="offcanvasImportStudent" aria-labelledby="offcanvasImportStudentLabel">
        <div class="offcanvas-header border-bottom">
            <h5 id="offcanvasImportStudentLabel" class="offcanvas-title">Import Murid</h5>
            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body mx-0 flex-grow-0 p-6 h-100">
            <div class="alert alert-info">
                Gunakan file dari tombol <strong>Download Format</strong>. Angkatan diisi tahun masuk, gender diisi <strong>L</strong> atau <strong>P</strong>. Tanggal lahir diisi <strong>dd-mm-yyyy</strong>. Data hasil import otomatis aktif.
            </div>
            <form method="POST" action="{{ route('students.import') }}" enctype="multipart/form-data">
                @csrf

                <div class="mb-4">
                    <label class="form-label" for="file">File Import</label>
                    <input type="file" class="form-control" id="file" name="file" accept=".csv,.xlsx,text/csv,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" required>
                    <div class="form-text">Upload file CSV atau Excel (.xlsx) sesuai format. Maksimal 2MB.</div>
                </div>

                <button type="submit" class="btn btn-primary me-2">Import</button>
                <button type="button" class="btn btn-label-secondary" data-bs-dismiss="offcanvas">Batal</button>
            </form>
        </div>
    </div>

    <div class="offcanvas offcanvas-start" tabindex="-1" id="offcanvasAddStudent" aria-labelledby="offcanvasAddStudentLabel">
        <div class="offcanvas-header border-bottom">
            <h5 id="offcanvasAddStudentLabel" class="offcanvas-title">Tambah Murid</h5>
            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body mx-0 flex-grow-0 p-6 h-100">
            <form method="POST" action="{{ route('students.store') }}" enctype="multipart/form-data">
                @csrf

                <h6 class="mb-3">Akun Login</h6>
                <div class="mb-4">
                    <label class="form-label" for="name">Nama Murid</label>
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

                <h6 class="mb-3 mt-2">Profil Murid</h6>
                <div class="mb-4">
                    <label class="form-label" for="photo">Foto Murid</label>
                    <input type="file" class="form-control" id="photo" name="photo" accept="image/jpeg,image/png,image/webp">
                    <div class="form-text">JPG, PNG, atau WebP maksimal 2MB.</div>
                </div>
                <div class="mb-4">
                    <label class="form-label" for="nis">NIS</label>
                    <input class="form-control" id="nis" name="nis" value="{{ old('nis') }}">
                </div>
                <div class="mb-4">
                    <label class="form-label" for="nisn">NISN</label>
                    <input class="form-control" id="nisn" name="nisn" value="{{ old('nisn') }}">
                </div>
                <div class="mb-4">
                    <label class="form-label" for="entry_year">Angkatan</label>
                    <input type="number" min="1900" max="2100" class="form-control" id="entry_year" name="entry_year" value="{{ old('entry_year') }}" placeholder="Contoh: 2026">
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
                    <label class="form-check-label" for="is_active">Murid aktif</label>
                </div>

                <button type="submit" class="btn btn-primary me-2">Simpan</button>
                <button type="button" class="btn btn-label-secondary" data-bs-dismiss="offcanvas">Batal</button>
            </form>
        </div>
    </div>
</div>

<script>
function confirmDeactivateStudent(id) {
    Swal.fire({
        title: 'Nonaktifkan murid?',
        text: 'Akun login murid juga akan dinonaktifkan.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Ya, nonaktifkan',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('deactivate-student-' + id).submit();
        }
    });
}

document.addEventListener('DOMContentLoaded', function () {
    const selectAll = document.getElementById('selectAllNametags');
    const printButton = document.getElementById('printSelectedNametags');
    const selectedCount = document.getElementById('selectedNametagCount');
    const checkboxes = Array.from(document.querySelectorAll('[data-student-nametag-checkbox]'));

    const selectedIds = () => checkboxes
        .filter((checkbox) => checkbox.checked)
        .map((checkbox) => checkbox.value);

    const syncSelectionState = () => {
        const ids = selectedIds();

        if (selectedCount) {
            selectedCount.textContent = ids.length;
        }

        if (printButton) {
            printButton.disabled = ids.length === 0;
        }

        if (selectAll) {
            selectAll.checked = checkboxes.length > 0 && ids.length === checkboxes.length;
            selectAll.indeterminate = ids.length > 0 && ids.length < checkboxes.length;
        }
    };

    selectAll?.addEventListener('change', function () {
        checkboxes.forEach((checkbox) => {
            checkbox.checked = selectAll.checked;
        });

        syncSelectionState();
    });

    checkboxes.forEach((checkbox) => {
        checkbox.addEventListener('change', syncSelectionState);
    });

    printButton?.addEventListener('click', function () {
        const ids = selectedIds();

        if (ids.length === 0) {
            Swal.fire({
                title: 'Pilih murid dulu',
                text: 'Ceklis murid yang nametag-nya ingin dicetak.',
                icon: 'info',
                confirmButtonText: 'OK'
            });
            return;
        }

        const url = new URL(printButton.dataset.printSelectedUrl, window.location.origin);
        ids.forEach((id) => url.searchParams.append('student_ids[]', id));
        window.open(url.toString(), '_blank');
    });

    syncSelectionState();
});
</script>
@endsection
