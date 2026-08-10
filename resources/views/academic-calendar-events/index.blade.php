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
            <h4 class="mb-1">Kalender Akademik</h4>
            <p class="text-muted mb-0">{{ $school->name }} - kelola tanggal khusus dan pengecualian presensi.</p>
        </div>

        <button class="btn btn-primary" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasAddCalendarEvent">
            <i class="bx bx-plus me-1"></i> Tambah Event
        </button>
    </div>

    <div class="card">
        <div class="card-body">
            <form method="GET" action="{{ route('academic-calendar-events.index') }}" class="mb-4">
                <div class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label" for="date_from">Dari</label>
                        <input type="date" class="form-control" id="date_from" name="date_from" value="{{ $filters['date_from'] }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label" for="date_to">Sampai</label>
                        <input type="date" class="form-control" id="date_to" name="date_to" value="{{ $filters['date_to'] }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label" for="type">Jenis</label>
                        <select class="form-select" id="type" name="type">
                            <option value="">Semua Jenis</option>
                            @foreach($types as $value => $label)
                                <option value="{{ $value }}" @selected($filters['type'] === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label" for="attendance_effect">Efek Presensi</label>
                        <select class="form-select" id="attendance_effect" name="attendance_effect">
                            <option value="">Semua Efek</option>
                            @foreach($attendanceEffects as $value => $label)
                                <option value="{{ $value }}" @selected($filters['attendance_effect'] === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12">
                        <div class="d-flex flex-wrap gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bx bx-filter-alt me-1"></i> Terapkan
                            </button>
                            <a href="{{ route('academic-calendar-events.index') }}" class="btn btn-label-secondary">
                                <i class="bx bx-refresh me-1"></i> Reset
                            </a>
                        </div>
                    </div>
                </div>
            </form>

            <div class="table-responsive text-nowrap">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Event</th>
                            <th>Tanggal</th>
                            <th>Jenis</th>
                            <th>Efek Presensi</th>
                            <th>Semester</th>
                            <th class="text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($events as $event)
                            <tr>
                                <td>
                                    <strong>{{ $event->title }}</strong>
                                    @if($event->notes)
                                        <div class="text-muted small text-truncate" style="max-width: 280px;">{{ $event->notes }}</div>
                                    @endif
                                </td>
                                <td>
                                    {{ $event->starts_at->format('d M Y') }}
                                    @if(! $event->starts_at->isSameDay($event->ends_at))
                                        - {{ $event->ends_at->format('d M Y') }}
                                    @endif
                                </td>
                                <td>{{ $types[$event->type] ?? $event->type }}</td>
                                <td>
                                    <span @class([
                                        'badge',
                                        'bg-label-secondary' => $event->attendance_effect === 'inherit',
                                        'bg-label-success' => $event->attendance_effect === 'attendance_day',
                                        'bg-label-warning' => $event->attendance_effect === 'non_attendance_day',
                                    ])>
                                        {{ $attendanceEffects[$event->attendance_effect] ?? $event->attendance_effect }}
                                    </span>
                                </td>
                                <td>{{ $event->semester?->name ?? '-' }}</td>
                                <td class="text-end">
                                    <button type="button" class="btn btn-sm btn-icon btn-label-primary" data-bs-toggle="offcanvas" data-bs-target="#offcanvasEditCalendarEvent{{ $event->id }}" title="Edit" aria-label="Edit">
                                        <i class="bx bx-edit-alt"></i>
                                    </button>
                                    <form method="POST" action="{{ route('academic-calendar-events.destroy', $event) }}" class="d-inline" id="delete-calendar-event-{{ $event->id }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button" class="btn btn-sm btn-icon btn-outline-danger" onclick="confirmDeleteCalendarEvent(@js($event->id))" title="Hapus" aria-label="Hapus">
                                            <i class="bx bx-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>

                            @include('academic-calendar-events.partials.form-offcanvas', [
                                'id' => 'offcanvasEditCalendarEvent'.$event->id,
                                'title' => 'Edit Event Kalender',
                                'action' => route('academic-calendar-events.update', $event),
                                'method' => 'PUT',
                                'event' => $event,
                            ])
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">Belum ada event kalender akademik.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <x-table-pagination :paginator="$events" label="event kalender" />
        </div>
    </div>

    @include('academic-calendar-events.partials.form-offcanvas', [
        'id' => 'offcanvasAddCalendarEvent',
        'title' => 'Tambah Event Kalender',
        'action' => route('academic-calendar-events.store'),
        'method' => 'POST',
        'event' => null,
    ])
</div>

<script>
function confirmDeleteCalendarEvent(id) {
    Swal.fire({
        title: 'Hapus event kalender?',
        text: 'Event yang dihapus tidak dapat dikembalikan.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Ya, hapus',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('delete-calendar-event-' + id).submit();
        }
    });
}
</script>
@endsection
