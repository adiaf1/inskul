@extends('layouts.app')

@section('content')


<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="py-4 mb-6">Manage Users</h4>
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
            <div class="dt-action-buttons text-xl-end text-lg-start text-md-end text-start d-flex align-items-center justify-content-end flex-md-row flex-column mb-6 mb-md-0 mt-n6 mt-md-0">
                <div id="DataTables_Table_0_filter" class="dataTables_filter">
                    <label><input type="search" name="search" class="form-control" placeholder="Cari Pengguna" aria-controls="DataTables_Table_0"></label>
                </div>
                <div class="dt-buttons btn-group flex-wrap ms-4">
                    <button class="btn btn-secondary add-new btn-primary" tabindex="0" aria-controls="DataTables_Table_0" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasAddUser">
                        <span><i class="bx bx-user-plus bx-sm me-0 me-sm-2"></i><span class="d-none d-sm-inline-block">Add</span></span>
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
                            <th class="sorting_disabled dt-checkboxes-cell dt-checkboxes-select-all" rowspan="1" colspan="1"><input type="checkbox" class="form-check-input"></th>
                            <th class="sorting sorting_desc" tabindex="0" aria-controls="DataTables_Table_0" rowspan="1" colspan="1">User</th>
                            <th class="sorting" tabindex="0" aria-controls="DataTables_Table_0" rowspan="1" colspan="1">Role</th>
                            <th class="sorting" tabindex="0" aria-controls="DataTables_Table_0" rowspan="1" colspan="1">Email</th>
                            <th class="sorting" tabindex="0" aria-controls="DataTables_Table_0" rowspan="1" colspan="1">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $user)
                            <tr>
                                <td><input type="checkbox" class="form-check-input"></td>
                                <td>{{ $user->name }}</td>
                                <td>{{ $user->roles->pluck('name')->join(', ') ?: 'No Role Assigned' }}</td>
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
                                            <form action="{{ route('users.destroy', $user->id) }}" method="POST" class="d-inline" id="delete-form-{{ $user->id }}">
                                                @csrf
                                                @method('DELETE')
                                                <button type="button" class="dropdown-item" onclick="confirmDelete({{ $user->id }})">
                                                    <i class="bx bx-trash me-1"></i> Delete
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                                                              </td>
                            </tr>

                            <!-- Offcanvas untuk mengedit pengguna -->
                            <div class="offcanvas offcanvas-start" tabindex="-1" id="offcanvasEditUser{{ $user->id }}" aria-labelledby="offcanvasEditUserLabel">
                                <div class="offcanvas-header border-bottom">
                                    <h5 id="offcanvasEditUserLabel" class="offcanvas-title">Edit User</h5>
                                    <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                                </div>
                                <div class="offcanvas-body mx-0 flex-grow-0 p-6 h-100">
                                    <form method="POST" action="{{ route('users.update', $user->id) }}">
                                        @csrf
                                        @method('PUT')
                                        <div class="mb-6">
                                            <label for="name" class="form-label">Name</label>
                                            <input type="text" class="form-control" id="name" name="name" value="{{ $user->name }}" required>
                                        </div>
                                        <div class="mb-6">
                                            <label for="email" class="form-label">Email</label>
                                            <input type="email" class="form-control" id="email" name="email" value="{{ $user->email }}" required>
                                        </div>
                                        <div class="mb-6">
                                            <label for="roles" class="form-label">Role</label>
                                            <select class="form-control" id="roles" name="roles[]" required>
                                                @foreach(\Spatie\Permission\Models\Role::all() as $role)
                                                    <option value="{{ $role->name }}" 
                                                        {{ in_array($role->name, $user->roles->pluck('name')->toArray()) ? 'selected' : '' }}>
                                                        {{ $role->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <button type="submit" class="btn btn-primary me-3">Update User</button>
                                        <button type="reset" class="btn btn-label-danger" data-bs-dismiss="offcanvas">Cancel</button>
                                    </form>
                                </div>
                            </div>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted">No Users Found</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                <div class="mt-3">
                    {{ $users->links('pagination::bootstrap-4') }}
                </div>
            </div>
        </div>

        <!-- Offcanvas untuk menambah pengguna -->
        <div class="offcanvas offcanvas-start" tabindex="-1" id="offcanvasAddUser" aria-labelledby="offcanvasAddUserLabel">
            <div class="offcanvas-header border-bottom">
                <h5 id="offcanvasAddUserLabel" class="offcanvas-title">Add User</h5>
                <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
            </div>
            <div class="offcanvas-body mx-0 flex-grow-0 p-6 h-100">
                <form method="POST" action="{{ route('users.store') }}">
                    @csrf
                    <div class="mb-6">
                        <label for="name" class="form-label">Name</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>
                    <div class="mb-6">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                    <div class="mb-6">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    <div class="mb-6">
                        <label for="role" class="form-label">Role</label>
                        <select class="form-control" id="role" name="role" required>
                            @foreach(\Spatie\Permission\Models\Role::all() as $role)
                                <option value="{{ $role->name }}">{{ $role->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary me-3">Create User</button>
                    <button type="reset" class="btn btn-label-danger" data-bs-dismiss="offcanvas">Cancel</button>
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