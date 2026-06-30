@extends('layouts.app')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    @include('exams.partials.sweetalert')

    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 py-4 mb-2">
        <div>
            <h4 class="mb-1">{{ $exam->title }}</h4>
            <p class="text-muted mb-0">{{ $exam->subject?->name }} - {{ $exam->classroom?->name }} - durasi {{ $exam->duration_minutes }} menit.</p>
        </div>
        <a href="{{ route('exams.index') }}" class="btn btn-label-secondary">Kembali</a>
    </div>

    <form method="POST" action="{{ route('exams.submit', $exam) }}" class="js-swal-confirm" data-swal-title="Kirim jawaban?" data-swal-text="Pastikan semua jawaban sudah benar. Setelah dikirim, jawaban tidak bisa diubah." data-swal-confirm="Ya, kirim">
        @csrf

        @forelse($questions as $question)
            @php($selectedAnswer = $attempt->answers->firstWhere('exam_question_id', $question->id))
            <div class="card mb-3">
                <div class="card-body">
                    <div class="d-flex justify-content-between gap-3 mb-3">
                        <h6 class="mb-0">Soal {{ $questions->firstItem() + $loop->index }}</h6>
                        <div class="d-flex flex-wrap justify-content-end gap-2">
                            <span class="badge bg-label-primary">{{ $question->points }} poin</span>
                            <span class="badge {{ $selectedAnswer ? 'bg-label-success' : 'bg-label-secondary' }}" data-answer-status="{{ $question->id }}">
                                {{ $selectedAnswer ? 'Tersimpan' : 'Draft' }}
                            </span>
                        </div>
                    </div>
                    <p class="mb-3">{{ $question->question_text }}</p>
                    @if($question->image_path)
                        <div class="mb-3">
                            <img src="{{ \App\Support\SchoolFileStorage::url($question->image_path) }}" alt="Gambar soal {{ $questions->firstItem() + $loop->index }}" class="img-fluid rounded border" style="max-height: 360px;">
                        </div>
                    @endif

                    <div class="row g-2">
                        @foreach($question->options as $option)
                            <div class="col-md-6">
                                <label class="border rounded d-flex align-items-start gap-2 p-3 h-100">
                                    <input class="form-check-input mt-1 js-autosave-answer" type="radio" name="answers[{{ $question->id }}]" value="{{ $option->id }}" data-question-id="{{ $question->id }}" @checked($selectedAnswer?->exam_option_id === $option->id)>
                                    <span><strong>{{ $option->label }}.</strong> {{ $option->option_text }}</span>
                                </label>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @empty
            <div class="card">
                <div class="card-body text-center text-muted py-5">Belum ada soal pada halaman ini.</div>
            </div>
        @endforelse

        <x-table-pagination :paginator="$questions" label="soal" />

        <div class="sticky-bottom bg-body py-3 mt-3 text-end">
            <button class="btn btn-primary">
                <i class="bx bx-send me-1"></i> Kirim Jawaban
            </button>
        </div>
    </form>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const token = @json(csrf_token());
        const saveUrl = @json(route('exams.answers.save', $exam));

        document.querySelectorAll('.js-autosave-answer').forEach(function (input) {
            input.addEventListener('change', function () {
                const status = document.querySelector('[data-answer-status="' + input.dataset.questionId + '"]');

                if (status) {
                    status.className = 'badge bg-label-warning';
                    status.textContent = 'Menyimpan...';
                }

                fetch(saveUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': token
                    },
                    body: JSON.stringify({
                        question_id: input.dataset.questionId,
                        option_id: input.value
                    })
                })
                    .then(function (response) {
                        if (!response.ok) {
                            return response.json().then(function (data) {
                                throw new Error(data.message || 'Jawaban gagal disimpan.');
                            });
                        }

                        return response.json();
                    })
                    .then(function () {
                        if (status) {
                            status.className = 'badge bg-label-success';
                            status.textContent = 'Tersimpan';
                        }
                    })
                    .catch(function (error) {
                        if (status) {
                            status.className = 'badge bg-label-danger';
                            status.textContent = 'Gagal tersimpan';
                        }

                        Swal.fire({
                            title: 'Gagal menyimpan',
                            text: error.message,
                            icon: 'error',
                            confirmButtonText: 'OK'
                        });
                    });
            });
        });
    });
</script>
@endsection
