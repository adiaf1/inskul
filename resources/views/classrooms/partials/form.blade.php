<div class="mb-4">
    <label class="form-label" for="{{ $mode }}_academic_year_id">Tahun Ajaran</label>
    <select class="form-select" id="{{ $mode }}_academic_year_id" name="academic_year_id" required>
        <option value="">Pilih tahun ajaran</option>
        @foreach($academicYears as $academicYear)
            <option value="{{ $academicYear->id }}" @selected((string) old('academic_year_id', $classroom?->academic_year_id) === (string) $academicYear->id)>
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
            <option value="{{ $semester->id }}" @selected((string) old('semester_id', $classroom?->semester_id) === (string) $semester->id)>
                {{ $semester->name }} - {{ $semester->academicYear?->name }}
            </option>
        @endforeach
    </select>
</div>

<div class="mb-4">
    <label class="form-label" for="{{ $mode }}_school_class_id">Kelas</label>
    <select class="form-select" id="{{ $mode }}_school_class_id" name="school_class_id" required>
        <option value="">Pilih kelas</option>
        @foreach($schoolClasses as $class)
            <option value="{{ $class->id }}" @selected((string) old('school_class_id', $classroom?->school_class_id) === (string) $class->id)>
                {{ $class->name }}{{ $class->level ? ' - '.$class->level : '' }}
            </option>
        @endforeach
    </select>
</div>

<div class="mb-4">
    <label class="form-label" for="{{ $mode }}_name">Nama Rombel</label>
    <input class="form-control" id="{{ $mode }}_name" name="name" value="{{ old('name', $classroom?->name) }}" placeholder="Contoh: VII A, X IPA 1" required>
</div>

<div class="mb-4">
    <label class="form-label" for="{{ $mode }}_homeroom_teacher_id">Wali Kelas</label>
    <select class="form-select" id="{{ $mode }}_homeroom_teacher_id" name="homeroom_teacher_id">
        <option value="">Belum ditentukan</option>
        @foreach($teachers as $teacher)
            <option value="{{ $teacher->id }}" @selected((string) old('homeroom_teacher_id', $classroom?->homeroom_teacher_id) === (string) $teacher->id)>
                {{ $teacher->user?->name }}
            </option>
        @endforeach
    </select>
</div>

<div class="mb-4">
    <label class="form-label" for="{{ $mode }}_capacity">Kapasitas</label>
    <input type="number" min="1" max="999" class="form-control" id="{{ $mode }}_capacity" name="capacity" value="{{ old('capacity', $classroom?->capacity) }}" placeholder="Contoh: 36">
</div>

<div class="mb-4">
    <label class="form-label" for="{{ $mode }}_student_cohort_filter">Filter Angkatan Murid</label>
    <select class="form-select" id="{{ $mode }}_student_cohort_filter" data-student-cohort-filter="{{ $mode }}">
        <option value="">Semua angkatan</option>
        @foreach($studentCohorts as $cohort)
            <option value="{{ $cohort }}">Angkatan {{ $cohort }}</option>
        @endforeach
    </select>
    <div class="form-text">Gunakan filter ini untuk mempersempit daftar murid saat memilih anggota rombel.</div>
</div>

<div class="mb-4">
    <label class="form-label" for="{{ $mode }}_student_ids">Anggota Murid</label>
    <select class="form-select" id="{{ $mode }}_student_ids" name="student_ids[]" multiple size="8" data-student-list="{{ $mode }}">
        @foreach($students as $student)
            <option value="{{ $student->id }}" data-cohort="{{ $student->entry_year }}" @selected(in_array((string) $student->id, array_map('strval', $selectedStudents), true))>
                {{ $student->user?->name }}{{ $student->nis ? ' - NIS '.$student->nis : '' }}{{ $student->entry_year ? ' - Angkatan '.$student->entry_year : '' }}
            </option>
        @endforeach
    </select>
    <div class="form-text">Tahan Ctrl untuk memilih lebih dari satu murid.</div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const filter = document.querySelector('[data-student-cohort-filter="{{ $mode }}"]');
    const list = document.querySelector('[data-student-list="{{ $mode }}"]');

    if (!filter || !list) {
        return;
    }

    const applyCohortFilter = () => {
        const cohort = filter.value;

        Array.from(list.options).forEach((option) => {
            option.hidden = cohort && option.dataset.cohort !== cohort && !option.selected;
        });
    };

    filter.addEventListener('change', applyCohortFilter);
    applyCohortFilter();
});
</script>

<div class="form-check mb-4">
    <input class="form-check-input" type="checkbox" id="{{ $mode }}_is_active" name="is_active" value="1" @checked(old('is_active', $classroom?->is_active ?? true))>
    <label class="form-check-label" for="{{ $mode }}_is_active">Rombel aktif</label>
</div>
