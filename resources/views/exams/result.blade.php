@extends('layouts.app')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    @include('exams.partials.sweetalert')

    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 py-4 mb-2">
        <div>
            <h4 class="mb-1">Hasil Ujian</h4>
            <p class="text-muted mb-0">{{ $exam->title }} - {{ $exam->subject?->name }}</p>
        </div>
        <a href="{{ route('exams.index') }}" class="btn btn-label-secondary">Kembali</a>
    </div>

    <div class="card mb-4">
        <div class="card-body d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div>
                <h5 class="mb-1">{{ $student->user?->name }}</h5>
                <p class="text-muted mb-0">Dikirim {{ $attempt->submitted_at?->format('d M Y H:i') ?? '-' }}</p>
            </div>
            <div class="text-end">
                <div class="display-6 fw-bold text-primary">{{ $attempt->score }}</div>
                <div class="text-muted">dari {{ $attempt->max_score }} poin</div>
            </div>
        </div>
    </div>

    @foreach($exam->questions as $question)
        @php($answer = $attempt->answers->firstWhere('exam_question_id', $question->id))
        <div class="card mb-3">
            <div class="card-body">
                <div class="d-flex justify-content-between gap-3 mb-3">
                    <h6 class="mb-0">Soal {{ $loop->iteration }}</h6>
                    <span class="badge {{ $answer?->is_correct ? 'bg-label-success' : 'bg-label-danger' }}">
                        {{ $answer?->points_awarded ?? 0 }} / {{ $question->points }}
                    </span>
                </div>
                <p>{{ $question->question_text }}</p>
                @if($question->image_path)
                    <div class="mb-3">
                        <img src="{{ \App\Support\SchoolFileStorage::url($question->image_path) }}" alt="Gambar soal {{ $loop->iteration }}" class="img-fluid rounded border" style="max-height: 360px;">
                    </div>
                @endif
                <div class="row g-2">
                    @foreach($question->options as $option)
                        <div class="col-md-6">
                            <div @class([
                                'border rounded p-3 h-100',
                                'border-success bg-label-success' => $option->is_correct,
                                'border-danger bg-label-danger' => $answer?->exam_option_id === $option->id && ! $option->is_correct,
                            ])>
                                <strong>{{ $option->label }}.</strong> {{ $option->option_text }}
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endforeach
</div>
@endsection
