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
            <h4 class="mb-1">Kelas</h4>
            <p class="text-muted mb-0">{{ $school->name }} - kelola kelas per tahun ajaran.</p>
        </div>

        <button class="btn btn-primary" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasAddClass">
            <i class="bx bx-plus me-1"></i> Tambah Kelas
        </button>
    </div>

    <div class="card">
        <div class="card-body">
            <form method="GET" action="{{ route('school-classes.index') }}" class="mb-4">
                <div class="d-flex flex-column flex-md-row gap-3">
                    <select name="academic_year_id" class="form-select" onchange="this.form.submit()">
                        <option value="">Semua Tahun Ajaran</option>
                        @foreach($academicYears as $academicYear)
                            <option value="{{ $academicYear->id }}" @selected((string) $academicYearId === (string) $academicYear->id)>
                                {{ $academicYear->name }}{{ $academicYear->is_active ? ' - Aktif' : '' }}
                            </option>
                        @endforeach
                    </select>
                    <select name="status" class="form-select" onchange="this.form.submit()">
                        <option value="">Semua Status</option>
                        <option value="active" @selected($status === 'active')>Aktif</option>
                        <option value="inactive" @selected($status === 'inactive')>Tidak Aktif</option>
                    </select>
                    <div class="input-group">
                        <input type="search" name="search" class="form-control" value="{{ request('search') }}" placeholder="Cari nama kelas atau tingkat">
                        <button class="btn btn-outline-primary" type="submit">Cari</button>
                    </div>
                </div>
            </form>

            <div class="table-responsive text-nowrap">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Nama Kelas</th>
                            <th>Tingkat</th>
                            <th>Tahun Ajaran</th>
                            <th>Status</th>
                            <th class="text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($schoolClasses as $class)
                            <tr>
                                <td><strong>{{ $class->name }}</strong></td>
                                <td>{{ $class->level ?? '-' }}</td>
                                <td>{{ $class->academicYear?->name ?? '-' }}</td>
                                <td>
                                    @if($class->is_active)
                                        <span class="badge bg-label-success">Aktif</span>
                                    @else
                                        <span class="badge bg-label-secondary">Tidak Aktif</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <button type="button" class="btn btn-sm btn-label-primary" data-bs-toggle="offcanvas" data-bs-target="#offcanvasEditClass{{ $class->id }}">
                                        Edit
                                    </button>
                                    <form method="POST" action="{{ route('school-classes.destroy', $class) }}" class="d-inline" id="delete-class-{{ $class->id }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="confirmDeleteClass({{ $class->id }})">
                                            Hapus
                                        </button>
                                    </form>
                                </td>
                            </tr>

                            <div class="offcanvas offcanvas-start" tabindex="-1" id="offcanvasEditClass{{ $class->id }}" aria-labelledby="offcanvasEditClassLabel{{ $class->id }}">
                                <div class="offcanvas-header border-bottom">
                                    <h5 id="offcanvasEditClassLabel{{ $class->id }}" class="offcanvas-title">Edit Kelas</h5>
                                    <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                                </div>
                                <div class="offcanvas-body mx-0 flex-grow-0 p-6 h-100">
                                    <form method="POST" action="{{ route('school-classes.update', $class) }}">
                                        @csrf
                                        @method('PUT')

                                        <div class="mb-4">
                                            <label class="form-label" for="edit_academic_year_id_{{ $class->id }}">Tahun Ajaran</label>
                                            <select class="form-select" id="edit_academic_year_id_{{ $class->id }}" name="academic_year_id" required>
                                                @foreach($academicYears as $academicYear)
                                                    <option value="{{ $academicYear->id }}" @selected((int) old('academic_year_id', $class->academic_year_id) === $academicYear->id)>
                                                        {{ $academicYear->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="mb-4">
                                            <label class="form-label" for="edit_name_{{ $class->id }}">Nama Kelas</label>
                                            <input class="form-control" id="edit_name_{{ $class->id }}" name="name" value="{{ old('name', $class->name) }}" required>
                                        </div>

                                        <div class="mb-4">
                                            <label class="form-label" for="edit_level_{{ $class->id }}">Tingkat</label>
                                            <input class="form-control" id="edit_level_{{ $class->id }}" name="level" value="{{ old('level', $class->level) }}" placeholder="Contoh: X, XI, VII">
                                        </div>

                                        <div class="form-check mb-4">
                                            <input class="form-check-input" type="checkbox" id="edit_is_active_{{ $class->id }}" name="is_active" value="1" @checked(old('is_active', $class->is_active))>
                                            <label class="form-check-label" for="edit_is_active_{{ $class->id }}">Kelas aktif</label>
                                        </div>

                                        <button type="submit" class="btn btn-primary me-2">Simpan Perubahan</button>
                                        <button type="button" class="btn btn-label-secondary" data-bs-dismiss="offcanvas">Batal</button>
                                    </form>
                                </div>
                            </div>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-5 text-muted">Belum ada data kelas.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($schoolClasses->hasPages())
                <div class="mt-4">{{ $schoolClasses->links() }}</div>
            @endif
        </div>
    </div>

    <div class="offcanvas offcanvas-start" tabindex="-1" id="offcanvasAddClass" aria-labelledby="offcanvasAddClassLabel">
        <div class="offcanvas-header border-bottom">
            <h5 id="offcanvasAddClassLabel" class="offcanvas-title">Tambah Kelas</h5>
            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body mx-0 flex-grow-0 p-6 h-100">
            <form method="POST" action="{{ route('school-classes.store') }}">
                @csrf

                <div class="mb-4">
                    <label class="form-label" for="academic_year_id">Tahun Ajaran</label>
                    <select class="form-select" id="academic_year_id" name="academic_year_id" required>
                        <option value="">Pilih tahun ajaran</option>
                        @foreach($academicYears as $academicYear)
                            <option value="{{ $academicYear->id }}" @selected((int) old('academic_year_id') === $academicYear->id)>
                                {{ $academicYear->name }}{{ $academicYear->is_active ? ' - Aktif' : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-4">
                    <label class="form-label" for="name">Nama Kelas</label>
                    <input class="form-control" id="name" name="name" value="{{ old('name') }}" placeholder="Contoh: X RPL 1" required>
                </div>

                <div class="mb-4">
                    <label class="form-label" for="level">Tingkat</label>
                    <input class="form-control" id="level" name="level" value="{{ old('level') }}" placeholder="Contoh: X, XI, VII">
                </div>

                <div class="form-check mb-4">
                    <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" @checked(old('is_active', true))>
                    <label class="form-check-label" for="is_active">Kelas aktif</label>
                </div>

                <button type="submit" class="btn btn-primary me-2">Simpan</button>
                <button type="button" class="btn btn-label-secondary" data-bs-dismiss="offcanvas">Batal</button>
            </form>
        </div>
    </div>
</div>

<script>
function confirmDeleteClass(id) {
    Swal.fire({
        title: 'Hapus kelas?',
        text: 'Data kelas yang dihapus tidak dapat dikembalikan.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Ya, hapus',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('delete-class-' + id).submit();
        }
    });
}
</script>
@endsection
