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
            <h4 class="mb-1">Mata Pelajaran</h4>
            <p class="text-muted mb-0">{{ $school->name }} - kelola daftar mata pelajaran sekolah.</p>
        </div>

        <button class="btn btn-primary" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasAddSubject">
            <i class="bx bx-plus me-1"></i> Tambah Mata Pelajaran
        </button>
    </div>

    <div class="card">
        <div class="card-body">
            <form method="GET" action="{{ route('subjects.index') }}" class="mb-4">
                <div class="d-flex flex-column flex-md-row gap-3">
                    <select name="status" class="form-select" onchange="this.form.submit()">
                        <option value="">Semua Status</option>
                        <option value="active" @selected($status === 'active')>Aktif</option>
                        <option value="inactive" @selected($status === 'inactive')>Tidak Aktif</option>
                    </select>
                    <div class="input-group">
                        <input type="search" name="search" class="form-control" value="{{ request('search') }}" placeholder="Cari nama atau kode mapel">
                        <button class="btn btn-outline-primary" type="submit">Cari</button>
                    </div>
                </div>
            </form>

            <div class="table-responsive text-nowrap">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Nama Mata Pelajaran</th>
                            <th>Kode</th>
                            <th>Status</th>
                            <th class="text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($subjects as $subject)
                            <tr>
                                <td><strong>{{ $subject->name }}</strong></td>
                                <td>{{ $subject->code ?? '-' }}</td>
                                <td>
                                    @if($subject->is_active)
                                        <span class="badge bg-label-success">Aktif</span>
                                    @else
                                        <span class="badge bg-label-secondary">Tidak Aktif</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <button type="button" class="btn btn-sm btn-icon btn-label-primary" data-bs-toggle="offcanvas" data-bs-target="#offcanvasEditSubject{{ $subject->id }}" title="Edit" aria-label="Edit">
                                        <i class="bx bx-edit-alt"></i>
                                    </button>
                                    <form method="POST" action="{{ route('subjects.destroy', $subject) }}" class="d-inline" id="delete-subject-{{ $subject->id }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button" class="btn btn-sm btn-icon btn-outline-danger" onclick="confirmDeleteSubject(@js($subject->id))" title="Hapus" aria-label="Hapus">
                                            <i class="bx bx-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>

                            <div class="offcanvas offcanvas-start" tabindex="-1" id="offcanvasEditSubject{{ $subject->id }}" aria-labelledby="offcanvasEditSubjectLabel{{ $subject->id }}">
                                <div class="offcanvas-header border-bottom">
                                    <h5 id="offcanvasEditSubjectLabel{{ $subject->id }}" class="offcanvas-title">Edit Mata Pelajaran</h5>
                                    <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                                </div>
                                <div class="offcanvas-body mx-0 flex-grow-0 p-6 h-100">
                                    <form method="POST" action="{{ route('subjects.update', $subject) }}">
                                        @csrf
                                        @method('PUT')

                                        <div class="mb-4">
                                            <label class="form-label" for="edit_name_{{ $subject->id }}">Nama Mata Pelajaran</label>
                                            <input class="form-control" id="edit_name_{{ $subject->id }}" name="name" value="{{ old('name', $subject->name) }}" required>
                                        </div>

                                        <div class="mb-4">
                                            <label class="form-label" for="edit_code_{{ $subject->id }}">Kode</label>
                                            <input class="form-control" id="edit_code_{{ $subject->id }}" name="code" value="{{ old('code', $subject->code) }}" placeholder="Contoh: MTK">
                                        </div>

                                        <div class="form-check mb-4">
                                            <input class="form-check-input" type="checkbox" id="edit_is_active_{{ $subject->id }}" name="is_active" value="1" @checked(old('is_active', $subject->is_active))>
                                            <label class="form-check-label" for="edit_is_active_{{ $subject->id }}">Mata pelajaran aktif</label>
                                        </div>

                                        <button type="submit" class="btn btn-primary me-2">Simpan Perubahan</button>
                                        <button type="button" class="btn btn-label-secondary" data-bs-dismiss="offcanvas">Batal</button>
                                    </form>
                                </div>
                            </div>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center py-5 text-muted">Belum ada data mata pelajaran.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <x-table-pagination :paginator="$subjects" label="mata pelajaran" />
        </div>
    </div>

    <div class="offcanvas offcanvas-start" tabindex="-1" id="offcanvasAddSubject" aria-labelledby="offcanvasAddSubjectLabel">
        <div class="offcanvas-header border-bottom">
            <h5 id="offcanvasAddSubjectLabel" class="offcanvas-title">Tambah Mata Pelajaran</h5>
            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body mx-0 flex-grow-0 p-6 h-100">
            <form method="POST" action="{{ route('subjects.store') }}">
                @csrf

                <div class="mb-4">
                    <label class="form-label" for="name">Nama Mata Pelajaran</label>
                    <input class="form-control" id="name" name="name" value="{{ old('name') }}" placeholder="Contoh: Matematika" required>
                </div>

                <div class="mb-4">
                    <label class="form-label" for="code">Kode</label>
                    <input class="form-control" id="code" name="code" value="{{ old('code') }}" placeholder="Contoh: MTK">
                </div>

                <div class="form-check mb-4">
                    <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" @checked(old('is_active', true))>
                    <label class="form-check-label" for="is_active">Mata pelajaran aktif</label>
                </div>

                <button type="submit" class="btn btn-primary me-2">Simpan</button>
                <button type="button" class="btn btn-label-secondary" data-bs-dismiss="offcanvas">Batal</button>
            </form>
        </div>
    </div>
</div>

<script>
function confirmDeleteSubject(id) {
    Swal.fire({
        title: 'Hapus mata pelajaran?',
        text: 'Data mata pelajaran yang dihapus tidak dapat dikembalikan.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Ya, hapus',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('delete-subject-' + id).submit();
        }
    });
}
</script>
@endsection
