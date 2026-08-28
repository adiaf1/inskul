@extends('layouts.app')

@section('content')


<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="py-4 mb-6">Kelola Pengguna</h4>
    <div class="card">

        <div class="card-body">
            <div class="mb-3 d-flex justify-content-between align-items-center">
                <!-- Tombol untuk menambah pengguna baru -->
            </div>
            <!-- Toast untuk notifikasi -->
            <div
                class="bs-toast toast toast-ex animate__animated my-2 fade {{ session('success') ? 'bg-success' : ($errors->any() ? 'bg-danger' : 'bg-primary') }} animate__bounceInDown show ">
                <div id="successToast" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
                    <div class="toast-header">
                        <strong class="me-auto">Notifikasi</strong>
                        <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
                    </div>
                    <div class="toast-body">
                        <span id="toast-message"></span>
                    </div>
                </div>
            </div>

            @if(session('success'))
                <script>
                    document.addEventListener('DOMContentLoaded', function () {
                        document.getElementById('toast-message').innerText =
                            '{{ session('success') }}';
                        var toast = new bootstrap.Toast(document.getElementById('successToast'));
                        toast.show();
                    });

                </script>
            @endif

            @if($errors->any())
                <script>
                    document.addEventListener('DOMContentLoaded', function () {
                        document.getElementById('toast-message').innerText = '{{ $errors->first() }}';
                        var toast = new bootstrap.Toast(document.getElementById('successToast'));
                        toast.show();
                    });

                </script>
            @endif
<div class="row mb-4">
    <div class="col-md-12">
        <form id="search-form" action="{{ route('users.index') }}" method="GET">
            <div class="dt-action-buttons text-xl-end text-lg-start text-md-end text-start d-flex align-items-center justify-content-end flex-md-row flex-column gap-3 mb-6 mb-md-0 mt-n6 mt-md-0">
                <div>
                    <select name="school_id" class="form-select" onchange="this.form.submit()">
                        <option value="">Semua Sekolah Aktif</option>
                        @foreach($schools as $school)
                            <option value="{{ $school->id }}" @selected((string) $schoolId === (string) $school->id)>
                                {{ $school->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <select name="role" class="form-select" onchange="this.form.submit()">
                        <option value="">Semua Role</option>
                        @foreach($roles as $role)
                            <option value="{{ $role->name }}" @selected($roleName === $role->name)>
                                {{ [
                                    'super_admin' => 'Super Admin',
                                    'school_admin' => 'Admin Sekolah',
                                    'principal' => 'Kepala Sekolah',
                                    'teacher' => 'Guru',
                                    'student' => 'Murid',
                                    'parent' => 'Wali Murid',
                                ][$role->name] ?? $role->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div id="DataTables_Table_0_filter" class="dataTables_filter">
                    <label><input type="search" name="search" value="{{ request('search') }}" class="form-control" placeholder="Cari Pengguna" aria-controls="DataTables_Table_0"></label>
                </div>
                <div class="dt-buttons btn-group flex-wrap">
                    <button class="btn btn-secondary add-new btn-primary" tabindex="0" aria-controls="DataTables_Table_0" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasAddUser">
                        <span><i class="bx bx-user-plus bx-sm me-0 me-sm-2"></i><span class="d-none d-sm-inline-block">Tambah</span></span>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>



            <div class="card-datatable table-responsive">
                <table class="datatables-users table table-hover border-top dataTable no-footer dtr-column" id="DataTables_Table_0">
                    <thead>
                        <tr>
                            <th rowspan="1" colspan="1" style="width: 64px;">No</th>
                            <th class="sorting sorting_desc" tabindex="0" aria-controls="DataTables_Table_0" rowspan="1" colspan="1">Pengguna</th>
                            <th class="sorting" tabindex="0" aria-controls="DataTables_Table_0" rowspan="1" colspan="1">Username</th>
                            <th class="sorting" tabindex="0" aria-controls="DataTables_Table_0" rowspan="1" colspan="1">Role</th>
                            <th class="sorting" tabindex="0" aria-controls="DataTables_Table_0" rowspan="1" colspan="1">Sekolah</th>
                            <th class="sorting" tabindex="0" aria-controls="DataTables_Table_0" rowspan="1" colspan="1">Email</th>
                            <th class="sorting" tabindex="0" aria-controls="DataTables_Table_0" rowspan="1" colspan="1">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $user)
                            <tr>
                                <td>{{ $users->firstItem() + $loop->index }}</td>
                                <td>{{ $user->name }}</td>
                                <td><code>{{ $user->username ?? '-' }}</code></td>
                                <td>
                                    {{ $user->roles->pluck('name')->map(fn ($role) => [
                                        'super_admin' => 'Super Admin',
                                        'school_admin' => 'Admin Sekolah',
                                        'principal' => 'Kepala Sekolah',
                                        'teacher' => 'Guru',
                                        'student' => 'Murid',
                                        'parent' => 'Wali Murid',
                                    ][$role] ?? $role)->join(', ') ?: 'Belum ada role' }}
                                </td>
                                <td>{{ $user->schools->pluck('name')->join(', ') ?: '-' }}</td>
                                <td>{{ $user->email }}</td>
                                <td>
                                    <div class="dropdown">
                                        <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown" aria-expanded="false">
                                            <i class="bx bx-dots-vertical-rounded"></i>
                                        </button>
                                        <div class="dropdown-menu">
                                            <button type="button" class="dropdown-item" data-bs-toggle="offcanvas" data-bs-target="#offcanvasEditUser{{ $user->id }}" data-user="{{ json_encode($user) }}">
                                                <i class="bx bx-edit-alt me-1"></i> Edit
                                            </button>
                                            <button type="button" class="dropdown-item" data-bs-toggle="offcanvas" data-bs-target="#offcanvasResetPassword{{ $user->id }}">
                                                <i class="bx bx-key me-1"></i> Reset Kata Sandi
                                            </button>
                                            <form action="{{ route('users.destroy', $user->id) }}" method="POST" class="d-inline" id="delete-form-{{ $user->id }}">
                                                @csrf
                                                @method('DELETE')
                                                <button type="button" class="dropdown-item" onclick="confirmDelete(@js($user->id))">
                                                    <i class="bx bx-trash me-1"></i> Hapus
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                                                              </td>
                            </tr>

                            <!-- Offcanvas untuk mengedit pengguna -->
                            <div class="offcanvas offcanvas-start" tabindex="-1" id="offcanvasEditUser{{ $user->id }}" aria-labelledby="offcanvasEditUserLabel">
                                <div class="offcanvas-header border-bottom">
                                    <h5 id="offcanvasEditUserLabel" class="offcanvas-title">Edit Pengguna</h5>
                                    <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                                </div>
                                <div class="offcanvas-body mx-0 flex-grow-0 p-6 h-100">
                                    <form method="POST" action="{{ route('users.update', $user->id) }}">
                                        @csrf
                                        @method('PUT')
                                        <div class="mb-6">
                                            <label for="name_{{ $user->id }}" class="form-label">Nama</label>
                                            <input type="text" class="form-control" id="name_{{ $user->id }}" name="name" value="{{ $user->name }}" required>
                                        </div>
                                        <div class="mb-6">
                                            <label for="username_{{ $user->id }}" class="form-label">Username</label>
                                            <input type="text" class="form-control" id="username_{{ $user->id }}" name="username" value="{{ $user->username }}" required>
                                            <div class="form-text">Huruf, angka, titik, strip, dan underscore. Tanpa spasi.</div>
                                        </div>
                                        <div class="mb-6">
                                            <label for="email_{{ $user->id }}" class="form-label">Email</label>
                                            <input type="email" class="form-control" id="email_{{ $user->id }}" name="email" value="{{ $user->email }}" required>
                                        </div>
                                        <div class="mb-6">
                                            <label for="roles_{{ $user->id }}" class="form-label">Role</label>
                                            <select class="form-control" id="roles_{{ $user->id }}" name="roles[]" required>
                                                @foreach(\Spatie\Permission\Models\Role::all() as $role)
                                                    <option value="{{ $role->name }}" 
                                                        {{ in_array($role->name, $user->roles->pluck('name')->toArray()) ? 'selected' : '' }}>
                                                        {{ [
                                                            'super_admin' => 'Super Admin',
                                                            'school_admin' => 'Admin Sekolah',
                                                            'principal' => 'Kepala Sekolah',
                                                            'teacher' => 'Guru',
                                                            'student' => 'Murid',
                                                            'parent' => 'Wali Murid',
                                                        ][$role->name] ?? $role->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <button type="submit" class="btn btn-primary me-3">Perbarui Pengguna</button>
                                        <button type="reset" class="btn btn-label-danger" data-bs-dismiss="offcanvas">Batal</button>
                                    </form>
                                </div>
                            </div>

                            <!-- Offcanvas untuk reset kata sandi -->
                            <div class="offcanvas offcanvas-start" tabindex="-1" id="offcanvasResetPassword{{ $user->id }}" aria-labelledby="offcanvasResetPasswordLabel{{ $user->id }}">
                                <div class="offcanvas-header border-bottom">
                                    <h5 id="offcanvasResetPasswordLabel{{ $user->id }}" class="offcanvas-title">Reset Kata Sandi</h5>
                                    <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                                </div>
                                <div class="offcanvas-body mx-0 flex-grow-0 p-6 h-100">
                                    <div class="mb-4">
                                        <strong>{{ $user->name }}</strong>
                                        <div class="text-muted small">{{ $user->email }}</div>
                                    </div>

                                    <form method="POST" action="{{ route('users.reset-password', $user->id) }}">
                                        @csrf
                                        @method('PATCH')

                                        <div class="mb-6">
                                            <label for="reset_password_{{ $user->id }}" class="form-label">Kata Sandi Baru</label>
                                            <input type="password" class="form-control" id="reset_password_{{ $user->id }}" name="password" required autocomplete="new-password">
                                        </div>

                                        <div class="mb-6">
                                            <label for="reset_password_confirmation_{{ $user->id }}" class="form-label">Konfirmasi Kata Sandi Baru</label>
                                            <input type="password" class="form-control" id="reset_password_confirmation_{{ $user->id }}" name="password_confirmation" required autocomplete="new-password">
                                        </div>

                                        <button type="submit" class="btn btn-primary me-3">Reset Kata Sandi</button>
                                        <button type="button" class="btn btn-label-secondary" data-bs-dismiss="offcanvas">Batal</button>
                                    </form>
                                </div>
                            </div>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted">Pengguna tidak ditemukan</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                <x-table-pagination :paginator="$users" label="pengguna" />
            </div>
        </div>

        <!-- Offcanvas untuk menambah pengguna -->
        <div class="offcanvas offcanvas-start" tabindex="-1" id="offcanvasAddUser" aria-labelledby="offcanvasAddUserLabel">
            <div class="offcanvas-header border-bottom">
                <h5 id="offcanvasAddUserLabel" class="offcanvas-title">Tambah Pengguna</h5>
                <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
            </div>
            <div class="offcanvas-body mx-0 flex-grow-0 p-6 h-100">
                <form method="POST" action="{{ route('users.store') }}">
                    @csrf
                    <div class="mb-6">
                        <label for="name" class="form-label">Nama</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>
                    <div class="mb-6">
                        <label for="username" class="form-label">Username</label>
                        <input type="text" class="form-control" id="username" name="username" required>
                        <div class="form-text">Huruf, angka, titik, strip, dan underscore. Tanpa spasi.</div>
                    </div>
                    <div class="mb-6">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                    <div class="mb-6">
                        <label for="password" class="form-label">Kata Sandi</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    <div class="mb-6">
                        <label for="role" class="form-label">Role</label>
                        <select class="form-control" id="role" name="role" required>
                            @foreach(\Spatie\Permission\Models\Role::all() as $role)
                                <option value="{{ $role->name }}">
                                    {{ [
                                        'super_admin' => 'Super Admin',
                                        'school_admin' => 'Admin Sekolah',
                                        'principal' => 'Kepala Sekolah',
                                        'teacher' => 'Guru',
                                        'student' => 'Murid',
                                        'parent' => 'Wali Murid',
                                    ][$role->name] ?? $role->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary me-3">Buat Pengguna</button>
                    <button type="reset" class="btn btn-label-danger" data-bs-dismiss="offcanvas">Batal</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    // Menangani event enter pada input pencarian
    document.getElementById('search-form').addEventListener('keypress', function (e) {
        if (e.key === 'Enter') {
            e.preventDefault(); // Untuk mencegah pengiriman default jika tidak dalam form
            this.submit(); // Mengirim form pencarian
        }
    });

    // Konfirmasi penghapusan pengguna
    function confirmDelete(id) {
        Swal.fire({
            title: 'Apa Anda yakin?',
            text: "Data pengguna ini akan dihapus!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Ya, hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                // Jika dikonfirmasi, submit form
                document.getElementById('delete-form-' + id).submit();
            }
        });
    }
</script>

@endsection
