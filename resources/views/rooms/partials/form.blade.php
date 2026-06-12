<div class="mb-4">
    <label class="form-label" for="{{ $mode }}_name">Nama Ruangan</label>
    <input class="form-control" id="{{ $mode }}_name" name="name" value="{{ old('name', $room?->name) }}" placeholder="Contoh: Lab Komputer" required>
</div>

<div class="mb-4">
    <label class="form-label" for="{{ $mode }}_code">Kode</label>
    <input class="form-control" id="{{ $mode }}_code" name="code" value="{{ old('code', $room?->code) }}" placeholder="Contoh: LAB-KOM">
</div>

<div class="mb-4">
    <label class="form-label" for="{{ $mode }}_type">Jenis Ruangan</label>
    <select class="form-select" id="{{ $mode }}_type" name="type" required>
        @foreach($types as $value => $label)
            <option value="{{ $value }}" @selected(old('type', $room?->type ?? 'classroom') === $value)>{{ $label }}</option>
        @endforeach
    </select>
</div>

<div class="mb-4">
    <label class="form-label" for="{{ $mode }}_capacity">Kapasitas</label>
    <input type="number" min="1" max="9999" class="form-control" id="{{ $mode }}_capacity" name="capacity" value="{{ old('capacity', $room?->capacity) }}" placeholder="Contoh: 36">
</div>

<div class="mb-4">
    <label class="form-label" for="{{ $mode }}_location">Lokasi</label>
    <input class="form-control" id="{{ $mode }}_location" name="location" value="{{ old('location', $room?->location) }}" placeholder="Contoh: Lantai 2 Gedung A">
</div>

<div class="form-check mb-4">
    <input class="form-check-input" type="checkbox" id="{{ $mode }}_is_active" name="is_active" value="1" @checked(old('is_active', $room?->is_active ?? true))>
    <label class="form-check-label" for="{{ $mode }}_is_active">Ruangan aktif</label>
</div>
