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
            <h4 class="mb-1">Presensi Per Jadwal</h4>
            <p class="text-muted mb-0">{{ $school->name }} - absensi berdasarkan sesi pelajaran pada jadwal.</p>
        </div>

        <a href="{{ route('attendances.index') }}" class="btn btn-label-secondary">Kembali</a>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('attendances.schedule') }}" class="mb-4">
                <div class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label" for="attendance_date_filter">Tanggal</label>
                        <input type="date" class="form-control" id="attendance_date_filter" name="attendance_date" value="{{ $attendanceDate }}" required>
                    </div>
                    <div class="col-md-4">
                        <div class="text-muted small">Hari</div>
                        <strong>{{ $days[$selectedDay] ?? '-' }}</strong>
                    </div>
                    <div class="col-md-4">
                        <button type="submit" class="btn btn-label-primary w-100">
                            <i class="bx bx-filter-alt me-1"></i> Tampilkan Jadwal
                        </button>
                    </div>
                </div>
            </form>

            @if($schedules->isEmpty())
                <div class="alert alert-warning mb-4">
                    Belum ada jadwal yang bisa dipresensi pada tanggal ini. Jika Anda login sebagai guru, pastikan akun guru sudah terdaftar sebagai pengajar pada jadwal.
                </div>
            @endif

            <form method="POST" action="{{ route('attendances.schedule.open') }}">
                @csrf
                <input type="hidden" name="attendance_date" value="{{ $attendanceDate }}">

                <div class="row g-3 align-items-end">
                    <div class="col-md-9">
                        <label class="form-label" for="schedule_id">Jadwal</label>
                        <select class="form-select" id="schedule_id" name="schedule_id" required>
                            <option value="">Pilih jadwal {{ $days[$selectedDay] ?? '' }}</option>
                            @foreach($schedules as $schedule)
                                <option value="{{ $schedule->id }}" @selected((string) old('schedule_id') === (string) $schedule->id)>
                                    {{ substr($schedule->starts_at, 0, 5) }} - {{ substr($schedule->ends_at, 0, 5) }}
                                    | {{ $schedule->classroom?->name }}
                                    | {{ $schedule->subject?->name }}
                                    | {{ $schedule->teacher?->user?->name ?? 'Guru belum ditentukan' }}
                                </option>
                            @endforeach
                        </select>
                        @if($schedules->isEmpty())
                            <div class="form-text text-warning">Belum ada jadwal aktif pada hari {{ $days[$selectedDay] ?? '-' }}.</div>
                        @endif
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-primary w-100" @disabled($schedules->isEmpty())>
                            <i class="bx bx-time-five me-1"></i> Buka Presensi
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive text-nowrap">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Jadwal</th>
                            <th>Rombel</th>
                            <th>Mata Pelajaran</th>
                            <th>Guru</th>
                            <th>Status</th>
                            <th class="text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($sessions as $session)
                            <tr>
                                <td>{{ $session->attendance_date->format('d M Y') }}</td>
                                <td>
                                    <strong>{{ substr($session->starts_at, 0, 5) }} - {{ substr($session->ends_at, 0, 5) }}</strong>
                                    <div class="text-muted small">{{ $session->schedule ? ($days[$session->schedule->day_of_week] ?? '-') : '-' }}</div>
                                </td>
                                <td>{{ $session->classroom?->name ?? '-' }}</td>
                                <td>{{ $session->subject?->name ?? $session->schedule?->subject?->name ?? '-' }}</td>
                                <td>{{ $session->teacher?->user?->name ?? '-' }}</td>
                                <td>
                                    <span @class([
                                        'badge',
                                        'bg-label-secondary' => $session->status === 'draft',
                                        'bg-label-success' => $session->status === 'submitted',
                                        'bg-label-dark' => $session->status === 'locked',
                                    ])>
                                        {{ ['draft' => 'Draft', 'submitted' => 'Submitted', 'locked' => 'Dikunci'][$session->status] ?? ucfirst($session->status) }}
                                    </span>
                                </td>
                                <td class="text-end">
                                    <a href="{{ route('attendances.schedule.edit', $session) }}" class="btn btn-sm btn-icon btn-label-primary" title="Buka" aria-label="Buka">
                                        <i class="bx bx-edit-alt"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">Belum ada sesi presensi per jadwal.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <x-table-pagination :paginator="$sessions" label="presensi" />
        </div>
    </div>
</div>
@endsection
