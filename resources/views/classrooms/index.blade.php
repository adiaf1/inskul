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

        <button class="btn btn-primary" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasAddClassroom">
            <i class="bx bx-plus me-1"></i> Tambah Rombel
        </button>
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
                                    <button type="button" class="btn btn-sm btn-label-primary" data-bs-toggle="offcanvas" data-bs-target="#offcanvasEditClassroom{{ $classroom->id }}">
                                        Edit
                                    </button>
                                    <form method="POST" action="{{ route('classrooms.destroy', $classroom) }}" class="d-inline" id="delete-classroom-{{ $classroom->id }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="confirmDeleteClassroom({{ $classroom->id }})">
                                            Hapus
                                        </button>
                                    </form>
                                </td>
                            </tr>

                            <div class="offcanvas offcanvas-start" tabindex="-1" id="offcanvasEditClassroom{{ $classroom->id }}" aria-labelledby="offcanvasEditClassroomLabel{{ $classroom->id }}">
                                <div class="offcanvas-header border-bottom">
                                    <h5 id="offcanvasEditClassroomLabel{{ $classroom->id }}" class="offcanvas-title">Edit Rombel</h5>
                                    <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                                </div>
                                <div class="offcanvas-body mx-0 flex-grow-0 p-6 h-100">
                                    @php
                                        $selectedStudents = old('student_ids', $classroom->students->pluck('id')->map(fn ($id) => (string) $id)->all());
                                    @endphp
                                    <form method="POST" action="{{ route('classrooms.update', $classroom) }}">
                                        @csrf
                                        @method('PUT')

                                        @include('classrooms.partials.form', [
                                            'mode' => 'edit_'.$classroom->id,
                                            'classroom' => $classroom,
                                            'selectedStudents' => $selectedStudents,
                                        ])

                                        <button type="submit" class="btn btn-primary me-2">Simpan Perubahan</button>
                                        <button type="button" class="btn btn-label-secondary" data-bs-dismiss="offcanvas">Batal</button>
                                    </form>
                                </div>
                            </div>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">Belum ada data rombel.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($classrooms->hasPages())
                <div class="mt-4">{{ $classrooms->links() }}</div>
            @endif
        </div>
    </div>

    <div class="offcanvas offcanvas-start" tabindex="-1" id="offcanvasAddClassroom" aria-labelledby="offcanvasAddClassroomLabel">
        <div class="offcanvas-header border-bottom">
            <h5 id="offcanvasAddClassroomLabel" class="offcanvas-title">Tambah Rombel</h5>
            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body mx-0 flex-grow-0 p-6 h-100">
            <form method="POST" action="{{ route('classrooms.store') }}">
                @csrf

                @include('classrooms.partials.form', [
                    'mode' => 'create',
                    'classroom' => null,
                    'selectedStudents' => old('student_ids', []),
                ])

                <button type="submit" class="btn btn-primary me-2">Simpan</button>
                <button type="button" class="btn btn-label-secondary" data-bs-dismiss="offcanvas">Batal</button>
            </form>
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
