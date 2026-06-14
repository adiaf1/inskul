@extends('layouts.app')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    @if($errors->any())
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                Swal.fire({
                    title: 'Error!',
                    text: '{{ $errors->first() }}',
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
            });
        </script>
    @endif

    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 py-4 mb-2">
        <div>
            <h4 class="mb-1">Jadwal Mengajar</h4>
            <p class="text-muted mb-0">{{ $school->name }} - jadwal yang terdaftar untuk {{ $teacher->user?->name }}.</p>
        </div>
        <a href="{{ route('dashboard') }}" class="btn btn-label-secondary">Dashboard</a>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('teacher-schedules.index') }}">
                <div class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label" for="day_of_week">Hari</label>
                        <select class="form-select" id="day_of_week" name="day_of_week">
                            <option value="">Semua Hari</option>
                            @foreach($days as $dayValue => $dayLabel)
                                <option value="{{ $dayValue }}" @selected((string) $dayOfWeek === (string) $dayValue)>{{ $dayLabel }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-5">
                        <label class="form-label" for="classroom_id">Rombel</label>
                        <select class="form-select" id="classroom_id" name="classroom_id">
                            <option value="">Semua Rombel</option>
                            @foreach($classrooms as $classroom)
                                <option value="{{ $classroom->id }}" @selected((string) $classroomId === (string) $classroom->id)>{{ $classroom->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <button class="btn btn-primary w-100" type="submit">
                            <i class="bx bx-filter-alt me-1"></i> Tampilkan
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive text-nowrap">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Hari/Jam</th>
                            <th>Rombel</th>
                            <th>Mata Pelajaran</th>
                            <th>Ruangan</th>
                            <th>Periode</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($schedules as $schedule)
                            <tr>
                                <td>
                                    <strong>{{ $days[$schedule->day_of_week] ?? '-' }}</strong>
                                    <div class="text-muted small">{{ substr($schedule->starts_at, 0, 5) }} - {{ substr($schedule->ends_at, 0, 5) }}</div>
                                </td>
                                <td>{{ $schedule->classroom?->name ?? '-' }}</td>
                                <td>
                                    <strong>{{ $schedule->subject?->name ?? '-' }}</strong>
                                    @if($schedule->subject?->code)
                                        <div class="text-muted small">{{ $schedule->subject->code }}</div>
                                    @endif
                                </td>
                                <td>
                                    {{ $schedule->physicalRoom?->name ?: ($schedule->room ?: '-') }}
                                    @if($schedule->physicalRoom?->code)
                                        <div class="text-muted small">{{ $schedule->physicalRoom->code }}</div>
                                    @endif
                                </td>
                                <td>
                                    <div>{{ $schedule->academicYear?->name ?? '-' }}</div>
                                    <div class="text-muted small">{{ $schedule->semester?->name ?? '-' }}</div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-5">
                                    Belum ada jadwal mengajar yang terdaftar untuk akun guru ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
