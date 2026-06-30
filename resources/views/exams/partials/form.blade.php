<div class="row g-4">
    <div class="col-md-8">
        <label class="form-label" for="title">Judul Ujian</label>
        <input class="form-control" id="title" name="title" value="{{ old('title', $exam?->title) }}" required>
        @error('title')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-4">
        <label class="form-label" for="duration_minutes">Durasi Menit</label>
        <input type="number" min="1" max="600" class="form-control" id="duration_minutes" name="duration_minutes" value="{{ old('duration_minutes', $exam?->duration_minutes ?? 60) }}" required>
        @error('duration_minutes')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-4">
        <label class="form-label" for="subject_id">Mata Pelajaran</label>
        <select class="form-select" id="subject_id" name="subject_id" required>
            <option value="">Pilih Mapel</option>
            @foreach($subjects as $subject)
                <option value="{{ $subject->id }}" @selected(old('subject_id', $exam?->subject_id) === $subject->id)>{{ $subject->name }}</option>
            @endforeach
        </select>
        @error('subject_id')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-4">
        <label class="form-label" for="classroom_id">Rombel</label>
        <select class="form-select" id="classroom_id" name="classroom_id" required>
            <option value="">Pilih Rombel</option>
            @foreach($classrooms as $classroom)
                <option value="{{ $classroom->id }}" @selected(old('classroom_id', $exam?->classroom_id) === $classroom->id)>{{ $classroom->name }}</option>
            @endforeach
        </select>
        @error('classroom_id')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-4">
        <label class="form-label" for="teacher_id">Guru</label>
        <select class="form-select" id="teacher_id" name="teacher_id">
            <option value="">Tanpa Guru</option>
            @foreach($teachers as $teacher)
                <option value="{{ $teacher->id }}" @selected(old('teacher_id', $exam?->teacher_id) === $teacher->id)>{{ $teacher->user?->name ?? '-' }}</option>
            @endforeach
        </select>
        @error('teacher_id')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6">
        <label class="form-label" for="starts_at">Mulai</label>
        <input type="datetime-local" class="form-control" id="starts_at" name="starts_at" value="{{ old('starts_at', $exam?->starts_at?->format('Y-m-d\TH:i')) }}">
        @error('starts_at')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6">
        <label class="form-label" for="ends_at">Selesai</label>
        <input type="datetime-local" class="form-control" id="ends_at" name="ends_at" value="{{ old('ends_at', $exam?->ends_at?->format('Y-m-d\TH:i')) }}">
        @error('ends_at')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
    </div>
    <div class="col-12">
        <label class="form-label" for="description">Deskripsi</label>
        <textarea class="form-control" id="description" name="description" rows="3">{{ old('description', $exam?->description) }}</textarea>
        @error('description')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
    </div>
</div>
