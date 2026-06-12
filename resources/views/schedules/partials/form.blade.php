<div class="mb-4">
    <label class="form-label" for="{{ $mode }}_academic_year_id">Tahun Ajaran</label>
    <select class="form-select" id="{{ $mode }}_academic_year_id" name="academic_year_id" required>
        <option value="">Pilih tahun ajaran</option>
        @foreach($academicYears as $academicYear)
            <option value="{{ $academicYear->id }}" @selected((string) old('academic_year_id', $schedule?->academic_year_id) === (string) $academicYear->id)>
                {{ $academicYear->name }}{{ $academicYear->is_active ? ' - Aktif' : '' }}
            </option>
        @endforeach
    </select>
</div>

<div class="mb-4">
    <label class="form-label" for="{{ $mode }}_semester_id">Semester</label>
    <select class="form-select" id="{{ $mode }}_semester_id" name="semester_id" required>
        <option value="">Pilih semester</option>
        @foreach($semesters as $semester)
            <option value="{{ $semester->id }}" @selected((string) old('semester_id', $schedule?->semester_id) === (string) $semester->id)>
                {{ $semester->name }} - {{ $semester->academicYear?->name }}
            </option>
        @endforeach
    </select>
</div>

<div class="mb-4">
    <label class="form-label" for="{{ $mode }}_classroom_id">Rombel</label>
    <select class="form-select" id="{{ $mode }}_classroom_id" name="classroom_id" required>
        <option value="">Pilih rombel</option>
        @foreach($classrooms as $classroom)
            <option value="{{ $classroom->id }}" @selected((string) old('classroom_id', $schedule?->classroom_id) === (string) $classroom->id)>
                {{ $classroom->name }} - {{ $classroom->semester?->name }}
            </option>
        @endforeach
    </select>
</div>

<div class="mb-4">
    <label class="form-label" for="{{ $mode }}_subject_id">Mata Pelajaran</label>
    <select class="form-select" id="{{ $mode }}_subject_id" name="subject_id" required>
        <option value="">Pilih mata pelajaran</option>
        @foreach($subjects as $subject)
            <option value="{{ $subject->id }}" @selected((string) old('subject_id', $schedule?->subject_id) === (string) $subject->id)>
                {{ $subject->name }}{{ $subject->code ? ' - '.$subject->code : '' }}
            </option>
        @endforeach
    </select>
</div>

<div class="mb-4">
    <label class="form-label" for="{{ $mode }}_teacher_id">Guru Pengajar</label>
    <select class="form-select" id="{{ $mode }}_teacher_id" name="teacher_id">
        <option value="">Belum ditentukan</option>
        @foreach($teachers as $teacher)
            <option value="{{ $teacher->id }}" @selected((string) old('teacher_id', $schedule?->teacher_id) === (string) $teacher->id)>
                {{ $teacher->user?->name }}
            </option>
        @endforeach
    </select>
</div>

<div class="mb-4">
    <label class="form-label" for="{{ $mode }}_day_of_week">Hari</label>
    <select class="form-select" id="{{ $mode }}_day_of_week" name="day_of_week" required>
        <option value="">Pilih hari</option>
        @foreach($days as $dayValue => $dayLabel)
            <option value="{{ $dayValue }}" @selected((string) old('day_of_week', $schedule?->day_of_week) === (string) $dayValue)>{{ $dayLabel }}</option>
        @endforeach
    </select>
</div>

<div class="row">
    <div class="col-md-6 mb-4">
        <label class="form-label" for="{{ $mode }}_starts_at">Jam Mulai</label>
        <input type="time" class="form-control" id="{{ $mode }}_starts_at" name="starts_at" value="{{ old('starts_at', $schedule ? substr($schedule->starts_at, 0, 5) : '') }}" required>
    </div>
    <div class="col-md-6 mb-4">
        <label class="form-label" for="{{ $mode }}_ends_at">Jam Selesai</label>
        <input type="time" class="form-control" id="{{ $mode }}_ends_at" name="ends_at" value="{{ old('ends_at', $schedule ? substr($schedule->ends_at, 0, 5) : '') }}" required>
    </div>
</div>

<div class="mb-4">
    <label class="form-label" for="{{ $mode }}_room_id">Ruangan</label>
    <select class="form-select" id="{{ $mode }}_room_id" name="room_id">
        <option value="">Belum ditentukan</option>
        @foreach($rooms as $room)
            <option value="{{ $room->id }}" @selected((string) old('room_id', $schedule?->room_id) === (string) $room->id)>
                {{ $room->name }}{{ $room->code ? ' - '.$room->code : '' }}
            </option>
        @endforeach
    </select>
</div>

<div class="mb-4">
    <label class="form-label" for="{{ $mode }}_room">Catatan Ruang</label>
    <input class="form-control" id="{{ $mode }}_room" name="room" value="{{ old('room', $schedule?->room) }}" placeholder="Opsional, contoh: dekat perpustakaan">
</div>

<div class="mb-4">
    <label class="form-label" for="{{ $mode }}_notes">Catatan</label>
    <textarea class="form-control" id="{{ $mode }}_notes" name="notes" rows="3">{{ old('notes', $schedule?->notes) }}</textarea>
</div>

<div class="form-check mb-4">
    <input class="form-check-input" type="checkbox" id="{{ $mode }}_is_active" name="is_active" value="1" @checked(old('is_active', $schedule?->is_active ?? true))>
    <label class="form-check-label" for="{{ $mode }}_is_active">Jadwal aktif</label>
</div>
