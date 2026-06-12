@extends('layouts.app')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    @if(session('success') || $errors->any())
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                Swal.fire({
                    title: '{{ session('success') ? 'Sukses!' : 'Error!' }}',
                    text: '{{ session('success') ?: $errors->first() }}',
                    icon: '{{ session('success') ? 'success' : 'error' }}',
                    confirmButtonText: 'OK'
                });
            });
        </script>
    @endif

    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 py-4 mb-2">
        <div>
            <h4 class="mb-1">Ruangan</h4>
            <p class="text-muted mb-0">{{ $school->name }} - kelola master ruangan sekolah.</p>
        </div>

        <button class="btn btn-primary" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasAddRoom">
            <i class="bx bx-plus me-1"></i> Tambah Ruangan
        </button>
    </div>

    <div class="card">
        <div class="card-body">
            <form method="GET" action="{{ route('rooms.index') }}" class="mb-4">
                <div class="d-flex flex-column flex-md-row gap-3">
                    <select name="type" class="form-select" onchange="this.form.submit()">
                        <option value="">Semua Jenis</option>
                        @foreach($types as $value => $label)
                            <option value="{{ $value }}" @selected($type === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    <select name="status" class="form-select" onchange="this.form.submit()">
                        <option value="">Semua Status</option>
                        <option value="active" @selected($status === 'active')>Aktif</option>
                        <option value="inactive" @selected($status === 'inactive')>Tidak Aktif</option>
                    </select>
                    <div class="input-group">
                        <input type="search" name="search" class="form-control" value="{{ request('search') }}" placeholder="Cari nama, kode, lokasi">
                        <button class="btn btn-outline-primary" type="submit">Cari</button>
                    </div>
                </div>
            </form>

            <div class="table-responsive text-nowrap">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Ruangan</th>
                            <th>Jenis</th>
                            <th>Kapasitas</th>
                            <th>Lokasi</th>
                            <th>Status</th>
                            <th class="text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($rooms as $room)
                            <tr>
                                <td>
                                    <strong>{{ $room->name }}</strong>
                                    <div class="text-muted small">{{ $room->code ?: '-' }}</div>
                                </td>
                                <td>{{ $types[$room->type] ?? ucfirst($room->type) }}</td>
                                <td>{{ $room->capacity ?: '-' }}</td>
                                <td>{{ $room->location ?: '-' }}</td>
                                <td>
                                    @if($room->is_active)
                                        <span class="badge bg-label-success">Aktif</span>
                                    @else
                                        <span class="badge bg-label-secondary">Tidak Aktif</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <button type="button" class="btn btn-sm btn-label-primary" data-bs-toggle="offcanvas" data-bs-target="#offcanvasEditRoom{{ $room->id }}">
                                        Edit
                                    </button>
                                    <form method="POST" action="{{ route('rooms.destroy', $room) }}" class="d-inline" id="delete-room-{{ $room->id }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="confirmDeleteRoom({{ $room->id }})">
                                            Hapus
                                        </button>
                                    </form>
                                </td>
                            </tr>

                            <div class="offcanvas offcanvas-start" tabindex="-1" id="offcanvasEditRoom{{ $room->id }}" aria-labelledby="offcanvasEditRoomLabel{{ $room->id }}">
                                <div class="offcanvas-header border-bottom">
                                    <h5 id="offcanvasEditRoomLabel{{ $room->id }}" class="offcanvas-title">Edit Ruangan</h5>
                                    <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                                </div>
                                <div class="offcanvas-body mx-0 flex-grow-0 p-6 h-100">
                                    <form method="POST" action="{{ route('rooms.update', $room) }}">
                                        @csrf
                                        @method('PUT')

                                        @include('rooms.partials.form', [
                                            'mode' => 'edit_'.$room->id,
                                            'room' => $room,
                                        ])

                                        <button type="submit" class="btn btn-primary me-2">Simpan Perubahan</button>
                                        <button type="button" class="btn btn-label-secondary" data-bs-dismiss="offcanvas">Batal</button>
                                    </form>
                                </div>
                            </div>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">Belum ada data ruangan.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <x-table-pagination :paginator="$rooms" label="ruangan" />
        </div>
    </div>

    <div class="offcanvas offcanvas-start" tabindex="-1" id="offcanvasAddRoom" aria-labelledby="offcanvasAddRoomLabel">
        <div class="offcanvas-header border-bottom">
            <h5 id="offcanvasAddRoomLabel" class="offcanvas-title">Tambah Ruangan</h5>
            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body mx-0 flex-grow-0 p-6 h-100">
            <form method="POST" action="{{ route('rooms.store') }}">
                @csrf

                @include('rooms.partials.form', [
                    'mode' => 'create',
                    'room' => null,
                ])

                <button type="submit" class="btn btn-primary me-2">Simpan</button>
                <button type="button" class="btn btn-label-secondary" data-bs-dismiss="offcanvas">Batal</button>
            </form>
        </div>
    </div>
</div>

<script>
function confirmDeleteRoom(id) {
    Swal.fire({
        title: 'Hapus ruangan?',
        text: 'Data ruangan yang dihapus tidak dapat dikembalikan.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Ya, hapus',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('delete-room-' + id).submit();
        }
    });
}
</script>
@endsection
