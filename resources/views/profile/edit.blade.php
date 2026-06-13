@extends('layouts.app')

@section('content')

<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="py-4 mb-6">Edit Profil</h4>
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
    <div class="card mb-6">
        <div class="card-body">
            <!-- Form untuk mengedit pengguna -->
            <form method="POST" action="{{ route('profile.update', $user->id) }}" enctype="multipart/form-data">
                @csrf
                @method('PATCH')
                <div class="row g-6">

                    <!-- Unggah foto profil -->
                    <div class="col-md-12 d-flex align-items-start align-items-sm-center gap-6 mb-4 border-bottom pb-4">
                        <img src="{{ \App\Support\SchoolFileStorage::url($user->profile_picture) }}" alt="user-avatar" class="d-block w-px-100 h-px-100 rounded" id="uploadedAvatar">
                        <div class="button-wrapper">
                            <label for="upload" class="btn btn-primary me-3 mb-4" tabindex="0">
                                <span class="d-none d-sm-block">Unggah foto baru</span>
                                <i class="bx bx-upload d-block d-sm-none"></i>
                                <input type="file" id="upload" name="profile_picture" class="account-file-input" hidden accept="image/png, image/jpeg" onchange="previewImage(event)">
                            </label>
                            <div>Format JPG, GIF, atau PNG. Ukuran maksimal 800K</div>
                        </div>
                    </div>
                    <div class="row g-3">

                    {{-- Nama --}}
                    <div class="col-md-6">
                        <label for="name" class="form-label">Nama</label>
                        <input 
                            class="form-control" 
                            type="text" 
                            id="name" 
                            name="name" 
                            value="{{ old('name', $user->name) }}" 
                            required
                        >
                    </div>

                    {{-- Email --}}
                    <div class="col-md-6">
                        <label for="email" class="form-label">E-mail</label>
                        <input 
                            class="form-control" 
                            type="email" 
                            id="email" 
                            name="email" 
                            value="{{ old('email', $user->email) }}" 
                            required
                        >
                    </div>

                    {{-- Bagian kata sandi --}}
                    <div class="col-12">
                        <label class="form-label d-block">Ubah Kata Sandi (opsional)</label>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <input 
                                    type="password" 
                                    class="form-control" 
                                    id="old_password" 
                                    name="old_password" 
                                    placeholder="Kata sandi saat ini"
                                >
                            </div>
                            <div class="col-md-4">
                                <input 
                                    type="password" 
                                    class="form-control" 
                                    id="new_password" 
                                    name="new_password" 
                                    placeholder="Kata sandi baru"
                                >
                            </div>
                            <div class="col-md-4">
                                <input 
                                    type="password" 
                                    class="form-control" 
                                    id="confirm_password" 
                                    name="confirm_password" 
                                    placeholder="Konfirmasi kata sandi baru"
                                >
                            </div>
                        </div>
                    </div>

                    </div>


                </div>

                <div class="mt-6">
                    <button type="submit" class="btn btn-primary me-3">Simpan Perubahan</button>
                    <a href="{{ route('dashboard') }}" class="btn btn-label-secondary">Kembali</a>
                </div>
            </form>
        </div>
    </div>
</div>



<script>
function previewImage(event) {
    const img = document.getElementById('uploadedAvatar');
    img.src = URL.createObjectURL(event.target.files[0]);
}
</script>

@endsection
