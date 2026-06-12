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
            <h4 class="mb-1">Tahun Ajaran</h4>
            <p class="text-muted mb-0">{{ $school->name }} - kelola periode akademik sekolah.</p>
        </div>

        <button class="btn btn-primary" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasAddAcademicYear">
            <i class="bx bx-plus me-1"></i> Tambah Tahun Ajaran
        </button>
    </div>

    <div class="card">
        <div class="card-body">
            <form method="GET" action="{{ route('academic-years.index') }}" class="mb-4">
                <div class="input-group">
                    <input type="search" name="search" class="form-control" value="{{ request('search') }}" placeholder="Cari tahun ajaran">
                    <button class="btn btn-outline-primary" type="submit">Cari</button>
                </div>
            </form>

            <div class="table-responsive text-nowrap">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Nama</th>
                            <th>Mulai</th>
                            <th>Selesai</th>
                            <th>Status</th>
                            <th class="text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($academicYears as $academicYear)
                            <tr>
                                <td><strong>{{ $academicYear->name }}</strong></td>
                                <td>{{ $academicYear->starts_at->format('d M Y') }}</td>
                                <td>{{ $academicYear->ends_at->format('d M Y') }}</td>
                                <td>
                                    @if($academicYear->is_active)
                                        <span class="badge bg-label-success">Aktif</span>
                                    @else
                                        <span class="badge bg-label-secondary">Tidak Aktif</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <button type="button" class="btn btn-sm btn-label-primary" data-bs-toggle="offcanvas" data-bs-target="#offcanvasEditAcademicYear{{ $academicYear->id }}">
                                        Edit
                                    </button>
                                    <form method="POST" action="{{ route('academic-years.destroy', $academicYear) }}" class="d-inline" id="delete-academic-year-{{ $academicYear->id }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="confirmDeleteAcademicYear({{ $academicYear->id }})">
                                            Hapus
                                        </button>
                                    </form>
                                </td>
                            </tr>

                            <div class="offcanvas offcanvas-start" tabindex="-1" id="offcanvasEditAcademicYear{{ $academicYear->id }}" aria-labelledby="offcanvasEditAcademicYearLabel{{ $academicYear->id }}">
                                <div class="offcanvas-header border-bottom">
                                    <h5 id="offcanvasEditAcademicYearLabel{{ $academicYear->id }}" class="offcanvas-title">Edit Tahun Ajaran</h5>
                                    <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                                </div>
                                <div class="offcanvas-body mx-0 flex-grow-0 p-6 h-100">
                                    <form method="POST" action="{{ route('academic-years.update', $academicYear) }}">
                                        @csrf
                                        @method('PUT')

                                        <div class="mb-4">
                                            <label class="form-label" for="edit_name_{{ $academicYear->id }}">Nama Tahun Ajaran</label>
                                            <input class="form-control" id="edit_name_{{ $academicYear->id }}" name="name" value="{{ old('name', $academicYear->name) }}" required>
                                        </div>

                                        <div class="mb-4">
                                            <label class="form-label" for="edit_starts_at_{{ $academicYear->id }}">Tanggal Mulai</label>
                                            <input type="date" class="form-control" id="edit_starts_at_{{ $academicYear->id }}" name="starts_at" value="{{ old('starts_at', $academicYear->starts_at->format('Y-m-d')) }}" required>
                                        </div>

                                        <div class="mb-4">
                                            <label class="form-label" for="edit_ends_at_{{ $academicYear->id }}">Tanggal Selesai</label>
                                            <input type="date" class="form-control" id="edit_ends_at_{{ $academicYear->id }}" name="ends_at" value="{{ old('ends_at', $academicYear->ends_at->format('Y-m-d')) }}" required>
                                        </div>

                                        <div class="form-check mb-4">
                                            <input class="form-check-input" type="checkbox" id="edit_is_active_{{ $academicYear->id }}" name="is_active" value="1" @checked(old('is_active', $academicYear->is_active))>
                                            <label class="form-check-label" for="edit_is_active_{{ $academicYear->id }}">Jadikan tahun ajaran aktif</label>
                                        </div>

                                        <button type="submit" class="btn btn-primary me-2">Simpan Perubahan</button>
                                        <button type="button" class="btn btn-label-secondary" data-bs-dismiss="offcanvas">Batal</button>
                                    </form>
                                </div>
                            </div>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-5 text-muted">Belum ada data tahun ajaran.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <x-table-pagination :paginator="$academicYears" label="tahun ajaran" />
        </div>
    </div>

    <div class="offcanvas offcanvas-start" tabindex="-1" id="offcanvasAddAcademicYear" aria-labelledby="offcanvasAddAcademicYearLabel">
        <div class="offcanvas-header border-bottom">
            <h5 id="offcanvasAddAcademicYearLabel" class="offcanvas-title">Tambah Tahun Ajaran</h5>
            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body mx-0 flex-grow-0 p-6 h-100">
            <form method="POST" action="{{ route('academic-years.store') }}">
                @csrf

                <div class="mb-4">
                    <label class="form-label" for="name">Nama Tahun Ajaran</label>
                    <input class="form-control" id="name" name="name" value="{{ old('name') }}" placeholder="Contoh: 2026/2027" required>
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
                    <label class="form-check-label" for="is_active">Jadikan tahun ajaran aktif</label>
                </div>

                <button type="submit" class="btn btn-primary me-2">Simpan</button>
                <button type="button" class="btn btn-label-secondary" data-bs-dismiss="offcanvas">Batal</button>
            </form>
        </div>
    </div>
</div>

<script>
function confirmDeleteAcademicYear(id) {
    Swal.fire({
        title: 'Hapus tahun ajaran?',
        text: 'Data yang sudah dipakai semester atau kelas tidak bisa dihapus.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Ya, hapus',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('delete-academic-year-' + id).submit();
        }
    });
}
</script>
@endsection
