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
            <h4 class="mb-1">Registrasi Sekolah</h4>
            <p class="text-muted mb-0">Kelola pengajuan sekolah dan aktivasi admin sekolah.</p>
        </div>

        <form method="GET" action="{{ route('schools.index') }}" class="d-flex gap-2">
            <select class="form-select" name="status" onchange="this.form.submit()">
                <option value="">Semua Status</option>
                @foreach (['pending' => 'Menunggu', 'inactive' => 'Tidak Aktif', 'active' => 'Aktif', 'rejected' => 'Ditolak'] as $value => $label)
                    <option value="{{ $value }}" @selected($status === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </form>
    </div>

    <div class="card">
        <div class="table-responsive text-nowrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>Sekolah</th>
                        <th>Admin</th>
                        <th>Kontak</th>
                        <th>Status</th>
                        <th>Tanggal</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody class="table-border-bottom-0">
                    @forelse ($schools as $school)
                        @php
                            $admin = $school->users->first(fn ($user) => $user->hasRole('school_admin'));
                        @endphp
                        <tr>
                            <td>
                                <strong>{{ $school->name }}</strong>
                                <div class="text-muted small">
                                    {{ $school->level }}{{ $school->npsn ? ' - NPSN '.$school->npsn : '' }}
                                </div>
                                @if($school->address)
                                    <div class="text-muted small">{{ $school->address }}</div>
                                @endif
                            </td>
                            <td>
                                <strong>{{ $admin?->name ?? '-' }}</strong>
                                <div class="text-muted small">{{ $admin?->email ?? '-' }}</div>
                            </td>
                            <td>
                                <div>{{ $school->phone ?? '-' }}</div>
                                <div class="text-muted small">{{ $school->email ?? '-' }}</div>
                            </td>
                            <td>
                                <span @class([
                                    'badge',
                                    'bg-label-warning' => $school->status === 'pending',
                                    'bg-label-secondary' => $school->status === 'inactive',
                                    'bg-label-success' => $school->status === 'active',
                                    'bg-label-danger' => $school->status === 'rejected',
                                ])>
                                    {{ ['pending' => 'Menunggu', 'inactive' => 'Tidak Aktif', 'active' => 'Aktif', 'rejected' => 'Ditolak'][$school->status] ?? ucfirst($school->status) }}
                                </span>
                            </td>
                            <td>{{ $school->created_at->format('d M Y H:i') }}</td>
                            <td class="text-end">
                                @if($school->status === 'pending')
                                    <form method="POST" action="{{ route('schools.approve', $school) }}" class="d-inline">
                                        @csrf
                                        @method('PATCH')
                                        <button class="btn btn-sm btn-primary" type="submit">Approve</button>
                                    </form>
                                    <form method="POST" action="{{ route('schools.reject', $school) }}" class="d-inline">
                                        @csrf
                                        @method('PATCH')
                                        <button class="btn btn-sm btn-outline-danger" type="submit">Tolak</button>
                                    </form>
                                @elseif($school->status === 'inactive')
                                    <form method="POST" action="{{ route('schools.approve', $school) }}" class="d-inline">
                                        @csrf
                                        @method('PATCH')
                                        <button class="btn btn-sm btn-primary" type="submit">Aktifkan</button>
                                    </form>
                                @else
                                    <span class="text-muted">Selesai</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">Belum ada data registrasi sekolah.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="card-footer">
            <x-table-pagination :paginator="$schools" label="sekolah" />
        </div>
    </div>
</div>
@endsection
