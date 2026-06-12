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
            <h4 class="mb-1">Pengguna Sekolah</h4>
            <p class="text-muted mb-0">{{ $school->name }} - tambah akun kepala sekolah, guru, murid, dan wali murid.</p>
        </div>

        <button class="btn btn-primary" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasAddSchoolUser">
            <i class="bx bx-user-plus me-1"></i> Tambah Pengguna
        </button>
    </div>

    <div class="card">
        <div class="card-body">
            <form method="GET" action="{{ route('school-users.index') }}" class="mb-4">
                <div class="input-group">
                    <input type="search" name="search" class="form-control" value="{{ request('search') }}" placeholder="Cari nama atau email">
                    <button class="btn btn-outline-primary" type="submit">Cari</button>
                </div>
            </form>

            <div class="table-responsive text-nowrap">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Nama</th>
                            <th>Email</th>
                            <th>Jenis Akun</th>
                            <th>Status</th>
                            <th>Dibuat</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $user)
                            <tr>
                                <td><strong>{{ $user->name }}</strong></td>
                                <td>{{ $user->email }}</td>
                                <td>
                                    {{ $user->roles->pluck('name')->map(fn ($role) => [
                                        'principal' => 'Kepala Sekolah',
                                        'teacher' => 'Guru',
                                        'student' => 'Murid',
                                        'parent' => 'Wali Murid',
                                    ][$role] ?? $role)->join(', ') }}
                                </td>
                                <td>
                                    <span class="badge bg-label-success">{{ $user->status === 'active' ? 'Aktif' : ucfirst($user->status) }}</span>
                                </td>
                                <td>{{ $user->created_at->format('d M Y H:i') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-5 text-muted">Belum ada pengguna sekolah.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <x-table-pagination :paginator="$users" label="pengguna" />
        </div>
    </div>

    <div class="offcanvas offcanvas-start" tabindex="-1" id="offcanvasAddSchoolUser" aria-labelledby="offcanvasAddSchoolUserLabel">
        <div class="offcanvas-header border-bottom">
            <h5 id="offcanvasAddSchoolUserLabel" class="offcanvas-title">Tambah Pengguna Sekolah</h5>
            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body mx-0 flex-grow-0 p-6 h-100">
            <form method="POST" action="{{ route('school-users.store') }}">
                @csrf

                <div class="mb-4">
                    <label for="name" class="form-label">Nama</label>
                    <input type="text" class="form-control" id="name" name="name" value="{{ old('name') }}" required>
                </div>

                <div class="mb-4">
                    <label for="email" class="form-label">Email Masuk</label>
                    <input type="email" class="form-control" id="email" name="email" value="{{ old('email') }}" required>
                </div>

                <div class="mb-4">
                    <label for="role" class="form-label">Jenis Akun</label>
                    <select class="form-select" id="role" name="role" required>
                        <option value="">Pilih jenis akun</option>
                        @foreach($roles as $role)
                            <option value="{{ $role->name }}" @selected(old('role') === $role->name)>
                                {{ [
                                    'principal' => 'Kepala Sekolah',
                                    'teacher' => 'Guru',
                                    'student' => 'Murid',
                                    'parent' => 'Wali Murid',
                                ][$role->name] ?? $role->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-4 form-password-toggle">
                    <label for="password" class="form-label">Kata Sandi</label>
                    <div class="input-group input-group-merge">
                        <input type="password" class="form-control" id="password" name="password" required autocomplete="new-password">
                        <span class="input-group-text cursor-pointer"><i class="bx bx-hide"></i></span>
                    </div>
                </div>

                <div class="mb-4 form-password-toggle">
                    <label for="password_confirmation" class="form-label">Konfirmasi Kata Sandi</label>
                    <div class="input-group input-group-merge">
                        <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" required autocomplete="new-password">
                        <span class="input-group-text cursor-pointer"><i class="bx bx-hide"></i></span>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary me-2">Simpan</button>
                <button type="button" class="btn btn-label-secondary" data-bs-dismiss="offcanvas">Batal</button>
            </form>
        </div>
    </div>
</div>
@endsection
