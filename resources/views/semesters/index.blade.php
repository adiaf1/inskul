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
            <h4 class="mb-1">Semester</h4>
            <p class="text-muted mb-0">{{ $school->name }} - kelola semester aktif per tahun ajaran.</p>
        </div>

        <button class="btn btn-primary" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasAddSemester">
            <i class="bx bx-plus me-1"></i> Tambah Semester
        </button>
    </div>

    <div class="card">
        <div class="card-body">
            <form method="GET" action="{{ route('semesters.index') }}" class="mb-4">
                <div class="d-flex flex-column flex-md-row gap-3">
                    <select name="academic_year_id" class="form-select" onchange="this.form.submit()">
                        <option value="">Semua Tahun Ajaran</option>
                        @foreach($academicYears as $academicYear)
                            <option value="{{ $academicYear->id }}" @selected((string) $academicYearId === (string) $academicYear->id)>
                                {{ $academicYear->name }}{{ $academicYear->is_active ? ' - Aktif' : '' }}
                            </option>
                        @endforeach
                    </select>
                    <div class="input-group">
                        <input type="search" name="search" class="form-control" value="{{ request('search') }}" placeholder="Cari semester">
                        <button class="btn btn-outline-primary" type="submit">Cari</button>
                    </div>
                </div>
            </form>

            <div class="table-responsive text-nowrap">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Semester</th>
                            <th>Tahun Ajaran</th>
                            <th>Mulai</th>
                            <th>Selesai</th>
                            <th>Status</th>
                            <th class="text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($semesters as $semester)
                            <tr>
                                <td><strong>{{ $semester->name }}</strong></td>
                                <td>{{ $semester->academicYear?->name ?? '-' }}</td>
                                <td>{{ $semester->starts_at->format('d M Y') }}</td>
                                <td>{{ $semester->ends_at->format('d M Y') }}</td>
                                <td>
                                    @if($semester->is_active)
                                        <span class="badge bg-label-success">Aktif</span>
                                    @else
                                        <span class="badge bg-label-secondary">Tidak Aktif</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <button type="button" class="btn btn-sm btn-label-primary" data-bs-toggle="offcanvas" data-bs-target="#offcanvasEditSemester{{ $semester->id }}">
                                        Edit
                                    </button>
                                    <form method="POST" action="{{ route('semesters.destroy', $semester) }}" class="d-inline" id="delete-semester-{{ $semester->id }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="confirmDeleteSemester({{ $semester->id }})">
                                            Hapus
                                        </button>
                                    </form>
                                </td>
                            </tr>

                            <div class="offcanvas offcanvas-start" tabindex="-1" id="offcanvasEditSemester{{ $semester->id }}" aria-labelledby="offcanvasEditSemesterLabel{{ $semester->id }}">
                                <div class="offcanvas-header border-bottom">
                                    <h5 id="offcanvasEditSemesterLabel{{ $semester->id }}" class="offcanvas-title">Edit Semester</h5>
                                    <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                                </div>
                                <div class="offcanvas-body mx-0 flex-grow-0 p-6 h-100">
                                    <form method="POST" action="{{ route('semesters.update', $semester) }}">
                                        @csrf
                                        @method('PUT')

                                        <div class="mb-4">
                                            <label class="form-label" for="edit_academic_year_id_{{ $semester->id }}">Tahun Ajaran</label>
                                            <select class="form-select" id="edit_academic_year_id_{{ $semester->id }}" name="academic_year_id" required>
                                                @foreach($academicYears as $academicYear)
                                                    <option value="{{ $academicYear->id }}" @selected((int) old('academic_year_id', $semester->academic_year_id) === $academicYear->id)>
                                                        {{ $academicYear->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="mb-4">
                                            <label class="form-label" for="edit_name_{{ $semester->id }}">Nama Semester</label>
                                            <input class="form-control" id="edit_name_{{ $semester->id }}" name="name" value="{{ old('name', $semester->name) }}" required>
                                        </div>

                                        <div class="mb-4">
                                            <label class="form-label" for="edit_starts_at_{{ $semester->id }}">Tanggal Mulai</label>
                                            <input type="date" class="form-control" id="edit_starts_at_{{ $semester->id }}" name="starts_at" value="{{ old('starts_at', $semester->starts_at->format('Y-m-d')) }}" required>
                                        </div>

                                        <div class="mb-4">
                                            <label class="form-label" for="edit_ends_at_{{ $semester->id }}">Tanggal Selesai</label>
                                            <input type="date" class="form-control" id="edit_ends_at_{{ $semester->id }}" name="ends_at" value="{{ old('ends_at', $semester->ends_at->format('Y-m-d')) }}" required>
                                        </div>

                                        <div class="form-check mb-4">
                                            <input class="form-check-input" type="checkbox" id="edit_is_active_{{ $semester->id }}" name="is_active" value="1" @checked(old('is_active', $semester->is_active))>
                                            <label class="form-check-label" for="edit_is_active_{{ $semester->id }}">Jadikan semester aktif</label>
                                        </div>

                                        <button type="submit" class="btn btn-primary me-2">Simpan Perubahan</button>
                                        <button type="button" class="btn btn-label-secondary" data-bs-dismiss="offcanvas">Batal</button>
                                    </form>
                                </div>
                            </div>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">Belum ada data semester.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($semesters->hasPages())
                <div class="mt-4">{{ $semesters->links() }}</div>
            @endif
        </div>
    </div>

    <div class="offcanvas offcanvas-start" tabindex="-1" id="offcanvasAddSemester" aria-labelledby="offcanvasAddSemesterLabel">
        <div class="offcanvas-header border-bottom">
            <h5 id="offcanvasAddSemesterLabel" class="offcanvas-title">Tambah Semester</h5>
            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body mx-0 flex-grow-0 p-6 h-100">
            <form method="POST" action="{{ route('semesters.store') }}">
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
                    <label class="form-label" for="name">Nama Semester</label>
                    <input class="form-control" id="name" name="name" value="{{ old('name') }}" placeholder="Contoh: Ganjil" required>
                </div>

                <div class="mb-4">
                    <label class="form-label" for="starts_at">Tanggal Mulai</label>
                    <input type="date" class="form-control" id="starts_at" name="starts_at" value="{{ old('starts_at') }}" required>
                </div>

                <div class="mb-4">
                    <label class="form-label" for="ends_at">Tanggal Selesai</label>
                    <input type="date" class="form-control" id="ends_at" name="ends_at" value="{{ old('ends_at') }}" required>
                </div>

                <div class="form-check mb-4">
                    <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" @checked(old('is_active'))>
                    <label class="form-check-label" for="is_active">Jadikan semester aktif</label>
                </div>

                <button type="submit" class="btn btn-primary me-2">Simpan</button>
                <button type="button" class="btn btn-label-secondary" data-bs-dismiss="offcanvas">Batal</button>
            </form>
        </div>
    </div>
</div>

<script>
function confirmDeleteSemester(id) {
    Swal.fire({
        title: 'Hapus semester?',
        text: 'Data semester yang dihapus tidak dapat dikembalikan.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Ya, hapus',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('delete-semester-' + id).submit();
        }
    });
}
</script>
@endsection
