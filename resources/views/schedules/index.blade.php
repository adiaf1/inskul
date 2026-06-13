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
            <h4 class="mb-1">Jadwal</h4>
            <p class="text-muted mb-0">{{ $school->name }} - kelola jadwal pelajaran per rombel.</p>
        </div>

        <button class="btn btn-primary" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasAddSchedule">
            <i class="bx bx-plus me-1"></i> Tambah Jadwal
        </button>
    </div>

    <div class="card">
        <div class="card-body">
            <form method="GET" action="{{ route('schedules.index') }}" class="mb-4">
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
                    <select name="classroom_id" class="form-select" onchange="this.form.submit()">
                        <option value="">Semua Rombel</option>
                        @foreach($classrooms as $classroom)
                            <option value="{{ $classroom->id }}" @selected((string) $classroomId === (string) $classroom->id)>
                                {{ $classroom->name }}
                            </option>
                        @endforeach
                    </select>
                    <select name="room_id" class="form-select" onchange="this.form.submit()">
                        <option value="">Semua Ruangan</option>
                        @foreach($rooms as $room)
                            <option value="{{ $room->id }}" @selected((string) $roomId === (string) $room->id)>
                                {{ $room->name }}
                            </option>
                        @endforeach
                    </select>
                    <select name="day_of_week" class="form-select" onchange="this.form.submit()">
                        <option value="">Semua Hari</option>
                        @foreach($days as $dayValue => $dayLabel)
                            <option value="{{ $dayValue }}" @selected((string) $dayOfWeek === (string) $dayValue)>{{ $dayLabel }}</option>
                        @endforeach
                    </select>
                    <select name="status" class="form-select" onchange="this.form.submit()">
                        <option value="">Semua Status</option>
                        <option value="active" @selected($status === 'active')>Aktif</option>
                        <option value="inactive" @selected($status === 'inactive')>Tidak Aktif</option>
                    </select>
                </div>
            </form>

            <div class="table-responsive text-nowrap">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Hari/Jam</th>
                            <th>Rombel</th>
                            <th>Mata Pelajaran</th>
                            <th>Guru</th>
                            <th>Ruang</th>
                            <th>Status</th>
                            <th class="text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($schedules as $schedule)
                            <tr>
                                <td>
                                    <strong>{{ $days[$schedule->day_of_week] ?? '-' }}</strong>
                                    <div class="text-muted small">{{ substr($schedule->starts_at, 0, 5) }} - {{ substr($schedule->ends_at, 0, 5) }}</div>
                                </td>
                                <td>
                                    <div>{{ $schedule->classroom?->name ?? '-' }}</div>
                                    <div class="text-muted small">{{ $schedule->semester?->name ?? '-' }}</div>
                                </td>
                                <td>
                                    <strong>{{ $schedule->subject?->name ?? '-' }}</strong>
                                    <div class="text-muted small">{{ $schedule->subject?->code ?: '' }}</div>
                                </td>
                                <td>{{ $schedule->teacher?->user?->name ?? '-' }}</td>
                                <td>
                                    <div>{{ $schedule->physicalRoom?->name ?: ($schedule->room ?: '-') }}</div>
                                    <div class="text-muted small">{{ $schedule->physicalRoom?->code ?: '' }}</div>
                                </td>
                                <td>
                                    @if($schedule->is_active)
                                        <span class="badge bg-label-success">Aktif</span>
                                    @else
                                        <span class="badge bg-label-secondary">Tidak Aktif</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <button type="button" class="btn btn-sm btn-icon btn-label-primary" data-bs-toggle="offcanvas" data-bs-target="#offcanvasEditSchedule{{ $schedule->id }}" title="Edit" aria-label="Edit">
                                        <i class="bx bx-edit-alt"></i>
                                    </button>
                                    <form method="POST" action="{{ route('schedules.destroy', $schedule) }}" class="d-inline" id="delete-schedule-{{ $schedule->id }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button" class="btn btn-sm btn-icon btn-outline-danger" onclick="confirmDeleteSchedule(@js($schedule->id))" title="Hapus" aria-label="Hapus">
                                            <i class="bx bx-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>

                            <div class="offcanvas offcanvas-start" tabindex="-1" id="offcanvasEditSchedule{{ $schedule->id }}" aria-labelledby="offcanvasEditScheduleLabel{{ $schedule->id }}">
                                <div class="offcanvas-header border-bottom">
                                    <h5 id="offcanvasEditScheduleLabel{{ $schedule->id }}" class="offcanvas-title">Edit Jadwal</h5>
                                    <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                                </div>
                                <div class="offcanvas-body mx-0 flex-grow-0 p-6 h-100">
                                    <form method="POST" action="{{ route('schedules.update', $schedule) }}">
                                        @csrf
                                        @method('PUT')

                                        @include('schedules.partials.form', [
                                            'mode' => 'edit_'.$schedule->id,
                                            'schedule' => $schedule,
                                        ])

                                        <button type="submit" class="btn btn-primary me-2">Simpan Perubahan</button>
                                        <button type="button" class="btn btn-label-secondary" data-bs-dismiss="offcanvas">Batal</button>
                                    </form>
                                </div>
                            </div>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">Belum ada data jadwal.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <x-table-pagination :paginator="$schedules" label="jadwal" />
        </div>
    </div>

    <div class="offcanvas offcanvas-start" tabindex="-1" id="offcanvasAddSchedule" aria-labelledby="offcanvasAddScheduleLabel">
        <div class="offcanvas-header border-bottom">
            <h5 id="offcanvasAddScheduleLabel" class="offcanvas-title">Tambah Jadwal</h5>
            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body mx-0 flex-grow-0 p-6 h-100">
            <form method="POST" action="{{ route('schedules.store') }}">
                @csrf

                @include('schedules.partials.form', [
                    'mode' => 'create',
                    'schedule' => null,
                ])

                <button type="submit" class="btn btn-primary me-2">Simpan</button>
                <button type="button" class="btn btn-label-secondary" data-bs-dismiss="offcanvas">Batal</button>
            </form>
        </div>
    </div>
</div>

<script>
function confirmDeleteSchedule(id) {
    Swal.fire({
        title: 'Hapus jadwal?',
        text: 'Data jadwal yang dihapus tidak dapat dikembalikan.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Ya, hapus',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('delete-schedule-' + id).submit();
        }
    });
}
</script>
@endsection
