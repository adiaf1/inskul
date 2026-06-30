@extends('layouts.app')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    @include('exams.partials.sweetalert')

    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 py-4 mb-2">
        <div>
            <h4 class="mb-1">Ujian</h4>
            <p class="text-muted mb-0">{{ $school->name }} - {{ $role === 'student' ? 'daftar ujian aktif Anda.' : 'kelola ujian pilihan ganda per mata pelajaran.' }}</p>
        </div>

        @if(in_array($role, ['school_admin', 'teacher'], true))
            <a href="{{ route('exams.create') }}" class="btn btn-primary">
                <i class="bx bx-plus me-1"></i> Buat Ujian
            </a>
        @endif
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive text-nowrap">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Ujian</th>
                            <th>Mata Pelajaran</th>
                            <th>Rombel</th>
                            <th>Waktu</th>
                            <th>Status</th>
                            <th class="text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($exams as $exam)
                            @php($attempt = $role === 'student' ? $exam->attempts->first() : null)
                            <tr>
                                <td>
                                    <strong>{{ $exam->title }}</strong>
                                    @if($role !== 'student')
                                        <div class="text-muted small">{{ $exam->questions_count ?? 0 }} soal - {{ $exam->attempts_count ?? 0 }} pengerjaan</div>
                                    @else
                                        <div class="text-muted small">Durasi {{ $exam->duration_minutes }} menit</div>
                                    @endif
                                </td>
                                <td>{{ $exam->subject?->name ?? '-' }}</td>
                                <td>{{ $exam->classroom?->name ?? '-' }}</td>
                                <td>
                                    <div>{{ $exam->starts_at?->format('d M Y H:i') ?? 'Tanpa mulai' }}</div>
                                    <div class="text-muted small">s/d {{ $exam->ends_at?->format('d M Y H:i') ?? 'Tanpa batas' }}</div>
                                </td>
                                <td>
                                    @if($role === 'student' && $attempt)
                                        <span class="badge bg-label-success">Sudah Dikerjakan</span>
                                    @elseif($exam->status === 'published')
                                        <span class="badge bg-label-primary">Aktif</span>
                                    @elseif($exam->status === 'closed')
                                        <span class="badge bg-label-secondary">Ditutup</span>
                                    @else
                                        <span class="badge bg-label-warning">Draft</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    @if($role === 'student')
                                        @if($attempt?->status === 'submitted')
                                            <a href="{{ route('exams.result', $exam) }}" class="btn btn-sm btn-label-primary">Hasil</a>
                                        @elseif($exam->isOpen())
                                            <a href="{{ route('exams.take', $exam) }}" class="btn btn-sm btn-primary">Kerjakan</a>
                                        @else
                                            <span class="text-muted small">Belum tersedia</span>
                                        @endif
                                    @else
                                        <div class="d-inline-flex gap-1">
                                            <a href="{{ route('exams.results', $exam) }}" class="btn btn-sm btn-icon btn-label-info" title="Hasil">
                                                <i class="bx bx-bar-chart"></i>
                                            </a>
                                            <a href="{{ route('exams.edit', $exam) }}" class="btn btn-sm btn-icon btn-label-primary" title="Edit">
                                                <i class="bx bx-edit-alt"></i>
                                            </a>
                                            <form method="POST" action="{{ route('exams.destroy', $exam) }}" class="js-swal-confirm" data-swal-title="Hapus ujian?" data-swal-text="Ujian, soal, jawaban, dan hasil pengerjaan akan ikut dihapus." data-swal-confirm="Ya, hapus">
                                                @csrf
                                                @method('DELETE')
                                                <button class="btn btn-sm btn-icon btn-label-danger" title="Hapus">
                                                    <i class="bx bx-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-5">Belum ada ujian.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <x-table-pagination :paginator="$exams" label="ujian" />
        </div>
    </div>
</div>
@endsection
