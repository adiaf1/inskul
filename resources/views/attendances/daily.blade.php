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
            <h4 class="mb-1">Presensi Harian</h4>
            <p class="text-muted mb-0">{{ $school->name }} - absensi satu kali per hari berdasarkan rombel.</p>
        </div>

        <a href="{{ route('attendances.index') }}" class="btn btn-label-secondary">Kembali</a>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            @if($classrooms->isEmpty())
                <div class="alert alert-warning mb-4">
                    Belum ada rombel yang bisa dipresensi. Jika Anda login sebagai guru, pastikan akun guru sudah ditetapkan sebagai wali kelas pada data rombel.
                </div>
            @endif

            <form method="POST" action="{{ route('attendances.daily.open') }}">
                @csrf
                <div class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label" for="attendance_date">Tanggal</label>
                        <input type="date" class="form-control" id="attendance_date" name="attendance_date" value="{{ old('attendance_date', now()->format('Y-m-d')) }}" required>
                    </div>
                    <div class="col-md-5">
                        <label class="form-label" for="classroom_id">Rombel</label>
                        <select class="form-select" id="classroom_id" name="classroom_id" required>
                            <option value="">Pilih rombel</option>
                            @foreach($classrooms as $classroom)
                                <option value="{{ $classroom->id }}" @selected((string) old('classroom_id') === (string) $classroom->id)>
                                    {{ $classroom->name }} - {{ $classroom->semester?->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bx bx-calendar-check me-1"></i> Buka Presensi
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
                            <th>Rombel</th>
                            <th>Wali Kelas</th>
                            <th>Status</th>
                            <th class="text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($sessions as $session)
                            <tr>
                                <td>{{ $session->attendance_date->format('d M Y') }}</td>
                                <td>{{ $session->classroom?->name ?? '-' }}</td>
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
                                    <a href="{{ route('attendances.daily.edit', $session) }}" class="btn btn-sm btn-icon btn-label-primary" title="Buka" aria-label="Buka">
                                        <i class="bx bx-edit-alt"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-5 text-muted">Belum ada sesi presensi harian.</td>
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
