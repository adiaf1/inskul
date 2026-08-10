@php
    $eventKey = $event?->id ?? 'new';
@endphp

<div class="offcanvas offcanvas-start" tabindex="-1" id="{{ $id }}" aria-labelledby="{{ $id }}Label">
    <div class="offcanvas-header border-bottom">
        <h5 id="{{ $id }}Label" class="offcanvas-title">{{ $title }}</h5>
        <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body mx-0 flex-grow-0 p-6 h-100">
        <form method="POST" action="{{ $action }}">
            @csrf
            @if($method !== 'POST')
                @method($method)
            @endif

            <div class="mb-4">
                <label class="form-label" for="title_{{ $eventKey }}">Judul</label>
                <input class="form-control" id="title_{{ $eventKey }}" name="title" value="{{ old('title', $event?->title) }}" placeholder="Contoh: Libur Nasional" required>
            </div>

            <div class="mb-4">
                <label class="form-label" for="starts_at_{{ $eventKey }}">Tanggal Mulai</label>
                <input type="date" class="form-control" id="starts_at_{{ $eventKey }}" name="starts_at" value="{{ old('starts_at', $event?->starts_at?->format('Y-m-d')) }}" required>
            </div>

            <div class="mb-4">
                <label class="form-label" for="ends_at_{{ $eventKey }}">Tanggal Selesai</label>
                <input type="date" class="form-control" id="ends_at_{{ $eventKey }}" name="ends_at" value="{{ old('ends_at', $event?->ends_at?->format('Y-m-d')) }}" required>
            </div>

            <div class="mb-4">
                <label class="form-label" for="type_{{ $eventKey }}">Jenis</label>
                <select class="form-select" id="type_{{ $eventKey }}" name="type" required>
                    @foreach($types as $value => $label)
                        <option value="{{ $value }}" @selected(old('type', $event?->type ?? 'other') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div class="mb-4">
                <label class="form-label" for="attendance_effect_{{ $eventKey }}">Efek Presensi</label>
                <select class="form-select" id="attendance_effect_{{ $eventKey }}" name="attendance_effect" required>
                    @foreach($attendanceEffects as $value => $label)
                        <option value="{{ $value }}" @selected(old('attendance_effect', $event?->attendance_effect ?? 'inherit') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div class="mb-4">
                <label class="form-label" for="academic_year_id_{{ $eventKey }}">Tahun Ajaran</label>
                <select class="form-select" id="academic_year_id_{{ $eventKey }}" name="academic_year_id">
                    <option value="">Tidak ditautkan</option>
                    @foreach($academicYears as $academicYear)
                        <option value="{{ $academicYear->id }}" @selected((string) old('academic_year_id', $event?->academic_year_id) === (string) $academicYear->id)>
                            {{ $academicYear->name }}{{ $academicYear->is_active ? ' - Aktif' : '' }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="mb-4">
                <label class="form-label" for="semester_id_{{ $eventKey }}">Semester</label>
                <select class="form-select" id="semester_id_{{ $eventKey }}" name="semester_id">
                    <option value="">Tidak ditautkan</option>
                    @foreach($semesters as $semester)
                        <option value="{{ $semester->id }}" @selected((string) old('semester_id', $event?->semester_id) === (string) $semester->id)>
                            {{ $semester->name }} - {{ $semester->academicYear?->name ?? '-' }}{{ $semester->is_active ? ' - Aktif' : '' }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="mb-4">
                <label class="form-label" for="notes_{{ $eventKey }}">Catatan</label>
                <textarea class="form-control" id="notes_{{ $eventKey }}" name="notes" rows="3">{{ old('notes', $event?->notes) }}</textarea>
            </div>

            <button type="submit" class="btn btn-primary me-2">Simpan</button>
            <button type="button" class="btn btn-label-secondary" data-bs-dismiss="offcanvas">Batal</button>
        </form>
    </div>
</div>
