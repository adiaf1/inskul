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
            <h4 class="mb-1">Rombel</h4>
            <p class="text-muted mb-0">{{ $school->name }} - kelola rombongan belajar per tahun ajaran dan semester.</p>
        </div>

        <a href="{{ route('classrooms.create') }}" class="btn btn-primary">
            <i class="bx bx-plus me-1"></i> Tambah Rombel
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <form method="GET" action="{{ route('classrooms.index') }}" class="mb-4">
                <div class="d-flex flex-column flex-md-row gap-3">
                    <select name="academic_year_id" class="form-select" onchange="this.form.submit()">
                        <option value="">Semua Tahun Ajaran</option>
                        @foreach($academicYears as $academicYear)
                            <option value="{{ $academicYear->id }}" @selected((string) $academicYearId === (string) $academicYear->id)>
                                {{ $academicYear->name }}{{ $academicYear->is_active ? ' - Aktif' : '' }}
                            </option>
                        @endforeach
                    </select>
                    <select name="semester_id" class="form-select" onchange="this.form.submit()">
                        <option value="">Semua Semester</option>
                        @foreach($semesters as $semester)
                            <option value="{{ $semester->id }}" @selected((string) $semesterId === (string) $semester->id)>
                                {{ $semester->name }} - {{ $semester->academicYear?->name }}
                            </option>
                        @endforeach
                    </select>
                    <select name="status" class="form-select" onchange="this.form.submit()">
                        <option value="">Semua Status</option>
                        <option value="active" @selected($status === 'active')>Aktif</option>
                        <option value="inactive" @selected($status === 'inactive')>Tidak Aktif</option>
                    </select>
                    <div class="input-group">
                        <input type="search" name="search" class="form-control" value="{{ request('search') }}" placeholder="Cari rombel, kelas, wali kelas">
                        <button class="btn btn-outline-primary" type="submit">Cari</button>
                    </div>
                </div>
            </form>

            <div class="table-responsive text-nowrap">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Rombel</th>
                            <th>Periode</th>
                            <th>Wali Kelas</th>
                            <th>Murid</th>
                            <th>Status</th>
                            <th class="text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($classrooms as $classroom)
                            <tr>
                                <td>
                                    <strong>{{ $classroom->name }}</strong>
                                    <div class="text-muted small">{{ $classroom->schoolClass?->name ?? '-' }}</div>
                                </td>
                                <td>
                                    <div>{{ $classroom->academicYear?->name ?? '-' }}</div>
                                    <div class="text-muted small">{{ $classroom->semester?->name ?? '-' }}</div>
                                </td>
                                <td>{{ $classroom->homeroomTeacher?->user?->name ?? '-' }}</td>
                                <td>
                                    <div>{{ $classroom->students->count() }} murid</div>
                                    <div class="text-muted small">{{ $classroom->capacity ? 'Kapasitas: '.$classroom->capacity : '' }}</div>
                                </td>
                                <td>
                                    @if($classroom->is_active)
                                        <span class="badge bg-label-success">Aktif</span>
                                    @else
                                        <span class="badge bg-label-secondary">Tidak Aktif</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <a href="{{ route('classrooms.edit', $classroom) }}" class="btn btn-sm btn-icon btn-label-primary" title="Edit" aria-label="Edit">
                                        <i class="bx bx-edit-alt"></i>
                                    </a>
                                    <form method="POST" action="{{ route('classrooms.destroy', $classroom) }}" class="d-inline" id="delete-classroom-{{ $classroom->id }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button" class="btn btn-sm btn-icon btn-outline-danger" onclick="confirmDeleteClassroom({{ $classroom->id }})" title="Hapus" aria-label="Hapus">
                                            <i class="bx bx-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>

                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">Belum ada data rombel.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <x-table-pagination :paginator="$classrooms" label="rombel" />
        </div>
    </div>

</div>

<script>
function confirmDeleteClassroom(id) {
    Swal.fire({
        title: 'Hapus rombel?',
        text: 'Anggota murid pada rombel ini juga akan dilepas.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Ya, hapus',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('delete-classroom-' + id).submit();
        }
    });
}
</script>
@endsection
