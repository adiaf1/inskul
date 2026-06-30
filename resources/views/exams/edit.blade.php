@extends('layouts.app')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    @include('exams.partials.sweetalert')

    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 py-4 mb-2">
        <div>
            <h4 class="mb-1">Kelola Ujian</h4>
            <p class="text-muted mb-0">{{ $exam->title }} - {{ $exam->subject?->name }} / {{ $exam->classroom?->name }}</p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            @if($exam->status !== 'published')
                <form method="POST" action="{{ route('exams.publish', $exam) }}" class="js-swal-confirm" data-swal-title="Publish ujian?" data-swal-text="Setelah dipublish, ujian akan tampil untuk murid sesuai jadwal." data-swal-confirm="Ya, publish">
                    @csrf
                    @method('PATCH')
                    <button class="btn btn-primary">
                        <i class="bx bx-check-circle me-1"></i> Publish
                    </button>
                </form>
            @else
                <form method="POST" action="{{ route('exams.close', $exam) }}" class="js-swal-confirm" data-swal-title="Tutup ujian?" data-swal-text="Murid tidak bisa lagi mengerjakan ujian ini." data-swal-confirm="Ya, tutup">
                    @csrf
                    @method('PATCH')
                    <button class="btn btn-label-warning">
                        <i class="bx bx-lock me-1"></i> Tutup
                    </button>
                </form>
            @endif
            <a href="{{ route('exams.index') }}" class="btn btn-label-secondary">Kembali</a>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header d-flex align-items-center justify-content-between">
            <h5 class="mb-0">Informasi Ujian</h5>
            <span @class([
                'badge',
                'bg-label-primary' => $exam->status === 'published',
                'bg-label-secondary' => $exam->status === 'closed',
                'bg-label-warning' => $exam->status === 'draft',
            ])>{{ ucfirst($exam->status) }}</span>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('exams.update', $exam) }}">
                @csrf
                @method('PUT')
                @include('exams.partials.form', ['exam' => $exam])

                <div class="mt-4">
                    <button class="btn btn-primary">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2">
            <h5 class="mb-0">Import Soal</h5>
            <a href="{{ route('exams.questions.import-template', $exam) }}" class="btn btn-label-secondary">
                <i class="bx bx-download me-1"></i> Download Template
            </a>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('exams.questions.import', $exam) }}" enctype="multipart/form-data">
                @csrf
                <div class="row g-3 align-items-end">
                    <div class="col-md-5">
                        <label class="form-label" for="questions_file">File CSV Soal</label>
                        <input type="file" class="form-control" id="questions_file" name="questions_file" accept=".csv,text/csv" required>
                        <div class="form-text">Gunakan template CSV. Kolom boleh tidak berurutan selama nama header sesuai.</div>
                    </div>
                    <div class="col-md-5">
                        <label class="form-label" for="images_zip">ZIP Gambar Soal</label>
                        <input type="file" class="form-control" id="images_zip" name="images_zip" accept=".zip,application/zip">
                        <div class="form-text">Opsional. Jika tidak diupload atau gambar tidak ditemukan, soal tetap masuk tanpa gambar.</div>
                    </div>
                    <div class="col-md-2">
                        <button class="btn btn-primary">
                            <i class="bx bx-upload me-1"></i> Import Soal
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">Tambah Soal</h5>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('exams.questions.store', $exam) }}" enctype="multipart/form-data">
                @csrf
                @include('exams.partials.question-form', ['question' => null, 'buttonText' => 'Tambah Soal'])
            </form>
        </div>
    </div>

    <div class="d-flex align-items-center justify-content-between mb-3">
        <h5 class="mb-0">Daftar Soal</h5>
        <span class="badge bg-label-primary">{{ $exam->questions->count() }} soal</span>
    </div>

    @forelse($exam->questions as $question)
        <div class="card mb-3">
            <div class="card-body">
                <form method="POST" action="{{ route('exams.questions.update', [$exam, $question]) }}" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    @include('exams.partials.question-form', ['question' => $question, 'buttonText' => 'Simpan Soal'])
                </form>
                <form method="POST" action="{{ route('exams.questions.destroy', [$exam, $question]) }}" class="mt-3 js-swal-confirm" data-swal-title="Hapus soal?" data-swal-text="Soal dan pilihan jawabannya akan dihapus dari ujian ini." data-swal-confirm="Ya, hapus">
                    @csrf
                    @method('DELETE')
                    <button class="btn btn-sm btn-label-danger">
                        <i class="bx bx-trash me-1"></i> Hapus Soal
                    </button>
                </form>
            </div>
        </div>
    @empty
        <div class="card">
            <div class="card-body text-center text-muted py-5">Belum ada soal. Tambahkan minimal satu soal sebelum publish.</div>
        </div>
    @endforelse
</div>
@endsection
