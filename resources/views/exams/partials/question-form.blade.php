@php
    $optionMap = $question?->options?->keyBy('label') ?? collect();
    $correctOption = old('correct_option', $question?->options?->firstWhere('is_correct', true)?->label ?? 'A');
@endphp

<div class="row g-3">
    <div class="col-md-9">
        <label class="form-label">Teks Soal</label>
        <textarea class="form-control" name="question_text" rows="3" required>{{ old('question_text', $question?->question_text) }}</textarea>
    </div>
    <div class="col-md-3">
        <label class="form-label">Poin</label>
        <input type="number" min="1" max="100" class="form-control mb-3" name="points" value="{{ old('points', $question?->points ?? 1) }}" required>
        <label class="form-label">Urutan</label>
        <input type="number" min="1" max="1000" class="form-control" name="sort_order" value="{{ old('sort_order', $question?->sort_order ?? 1) }}" required>
    </div>

    @foreach(['A', 'B', 'C', 'D'] as $label)
        <div class="col-md-6">
            <label class="form-label">Pilihan {{ $label }}</label>
            <div class="input-group">
                <span class="input-group-text">
                    <input class="form-check-input mt-0" type="radio" name="correct_option" value="{{ $label }}" @checked($correctOption === $label) aria-label="Jawaban benar {{ $label }}">
                </span>
                <textarea class="form-control" name="options[{{ $label }}]" rows="2" required>{{ old("options.$label", $optionMap->get($label)?->option_text) }}</textarea>
            </div>
        </div>
    @endforeach
</div>

<div class="mt-3">
    <button class="btn btn-primary">{{ $buttonText }}</button>
</div>
