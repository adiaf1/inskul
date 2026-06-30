@extends('layouts.app')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    @include('exams.partials.sweetalert')

    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 py-4 mb-2">
        <div>
            <h4 class="mb-1">Hasil Ujian</h4>
            <p class="text-muted mb-0">{{ $exam->title }} - {{ $exam->subject?->name }} / {{ $exam->classroom?->name }}</p>
        </div>
        <a href="{{ route('exams.index') }}" class="btn btn-label-secondary">Kembali</a>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive text-nowrap">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Nama</th>
                            <th>Waktu Mulai</th>
                            <th>Waktu Submit</th>
                            <th>Status</th>
                            <th class="text-end">Nilai</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($attempts as $attempt)
                            <tr>
                                <td>
                                    <strong>{{ $attempt->student?->user?->name ?? '-' }}</strong>
                                    <div class="text-muted small">{{ $attempt->student?->nis ?: '-' }}</div>
                                </td>
                                <td>{{ $attempt->started_at?->format('d M Y H:i') ?? '-' }}</td>
                                <td>{{ $attempt->submitted_at?->format('d M Y H:i') ?? '-' }}</td>
                                <td>
                                    @if($attempt->status === 'submitted')
                                        <span class="badge bg-label-success">Submitted</span>
                                    @else
                                        <span class="badge bg-label-warning">Berjalan</span>
                                    @endif
                                </td>
                                <td class="text-end fw-semibold">{{ $attempt->score }} / {{ $attempt->max_score }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-5">Belum ada siswa yang mengerjakan ujian ini.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <x-table-pagination :paginator="$attempts" label="hasil ujian" />
        </div>
    </div>
</div>
@endsection
