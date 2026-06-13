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
            <h4 class="mb-1">Input Presensi Harian</h4>
            <p class="text-muted mb-0">
                {{ $session->classroom?->name }} - {{ $session->attendance_date->format('d M Y') }}
            </p>
        </div>

        <a href="{{ route('attendances.daily') }}" class="btn btn-label-secondary">Kembali</a>
    </div>

    <form method="POST" action="{{ route('attendances.daily.update', $session) }}">
        @csrf
        @method('PUT')

        <div class="card mb-4">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-3">
                        <div class="text-muted small">Rombel</div>
                        <strong>{{ $session->classroom?->name ?? '-' }}</strong>
                    </div>
                    <div class="col-md-3">
                        <div class="text-muted small">Tanggal</div>
                        <strong>{{ $session->attendance_date->format('d M Y') }}</strong>
                    </div>
                    <div class="col-md-3">
                        <div class="text-muted small">Wali Kelas</div>
                        <strong>{{ $session->teacher?->user?->name ?? '-' }}</strong>
                    </div>
                    <div class="col-md-3">
                        <div class="text-muted small">Status</div>
                        <span @class([
                            'badge',
                            'bg-label-secondary' => $session->status === 'draft',
                            'bg-label-success' => $session->status === 'submitted',
                            'bg-label-dark' => $session->status === 'locked',
                        ])>
                            {{ ['draft' => 'Draft', 'submitted' => 'Submitted', 'locked' => 'Dikunci'][$session->status] ?? ucfirst($session->status) }}
                        </span>
                    </div>
                </div>

                <div class="mt-4">
                    <label class="form-label" for="notes">Catatan Sesi</label>
                    <textarea class="form-control" id="notes" name="notes" rows="2">{{ old('notes', $session->notes) }}</textarea>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="table-responsive text-nowrap">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>Murid</th>
                                <th>NIS/NISN</th>
                                <th>Status</th>
                                <th>Catatan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($session->records->sortBy(fn ($record) => $record->student?->user?->name) as $record)
                                <tr>
                                    <td>
                                        <strong>{{ $record->student?->user?->name }}</strong>
                                        <div class="text-muted small">{{ $record->student?->user?->email }}</div>
                                    </td>
                                    <td>
                                        <div>{{ $record->student?->nis ?: '-' }}</div>
                                        <div class="text-muted small">{{ $record->student?->nisn ? 'NISN: '.$record->student?->nisn : '' }}</div>
                                    </td>
                                    <td style="min-width: 180px;">
                                        <select class="form-select" name="records[{{ $record->id }}][status]" @disabled($session->status === 'locked')>
                                            @foreach($recordStatuses as $value => $label)
                                                <option value="{{ $value }}" @selected(old("records.{$record->id}.status", $record->status) === $value)>{{ $label }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td style="min-width: 240px;">
                                        <input class="form-control" name="records[{{ $record->id }}][notes]" value="{{ old("records.{$record->id}.notes", $record->notes) }}" placeholder="Opsional" @disabled($session->status === 'locked')>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-5 text-muted">Belum ada murid pada sesi presensi ini.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="d-flex flex-wrap gap-2 mt-4">
                    @if($session->status !== 'locked')
                        <button type="submit" name="action" value="draft" class="btn btn-label-primary">
                            Simpan Draft
                        </button>
                        <button type="submit" name="action" value="submit" class="btn btn-primary">
                            Submit Presensi
                        </button>
                    @endif
                    <a href="{{ route('attendances.daily') }}" class="btn btn-label-secondary">Kembali</a>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection
