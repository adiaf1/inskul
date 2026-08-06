<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="light-style layout-wide customizer-hide" dir="ltr" data-theme="theme-default" data-bs-theme="light" data-assets-path="{{ asset('assets/') }}/" data-template="horizontal-menu-template" data-style="light">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />
    <title>Registrasi Sekolah - {{ config('app.name') }}</title>
    <meta name="description" content="" />
    <link rel="icon" type="image/png" href="{{ asset('assets/img/branding/logo.png') }}?v=inskul-20260630" />

    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet" />

    <link rel="stylesheet" href="{{ asset('assets/vendor/css/rtl/core.css') }}" class="template-customizer-core-css" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/css/rtl/theme-default.css') }}" class="template-customizer-theme-css" />
    <link rel="stylesheet" href="{{ asset('assets/css/demo.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/css/pages/page-auth.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/inskul-theme.css') }}?v=20260806" />

    <script src="{{ asset('assets/vendor/js/helpers.js') }}"></script>
    <script src="{{ asset('assets/vendor/js/template-customizer.js') }}"></script>
    <script src="{{ asset('assets/js/config.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>
    <div class="authentication-wrapper authentication-cover">
        <a href="{{ route('home') }}" class="app-brand auth-cover-brand gap-2">
            <span class="app-brand-logo demo">
                <img src="{{ asset('assets/img/branding/logo.png') }}" alt="{{ config('app.name') }}" style="height: 34px; width: auto;">
            </span>
            <span class="app-brand-text demo text-heading fw-bold">{{ config('app.name') }}</span>
        </a>

        <div class="authentication-inner row m-0">
            <div class="d-none d-lg-flex col-lg-6 col-xl-7 align-items-center p-5">
                <div class="w-100 d-flex justify-content-center">
                    <img src="{{ asset('assets/img/illustrations/boy-with-rocket-light.png') }}" class="img-fluid" alt="Registrasi sekolah" width="680" />
                </div>
            </div>

            @if($errors->any())
                <script>
                    Swal.fire({
                        title: 'Error!',
                        text: '{{ $errors->first() }}',
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                </script>
            @endif

            <div class="d-flex col-12 col-lg-6 col-xl-5 align-items-center authentication-bg p-sm-12 p-6">
                <div class="w-100 mx-auto mt-8 pt-5" style="max-width: 520px;">
                    <h4 class="mb-1">Registrasi Sekolah</h4>
                    <p class="mb-6">Ajukan sekolah dan akun admin sekolah. Akun aktif setelah disetujui Super Admin.</p>

                    <form class="mb-6" method="POST" action="{{ route('register') }}">
                        @csrf

                        <h6 class="mb-3">Data Sekolah</h6>

                        <div class="mb-4">
                            <label for="school_name" class="form-label">Nama Sekolah</label>
                            <input type="text" class="form-control" id="school_name" name="school_name" value="{{ old('school_name') }}" placeholder="Contoh: SMA Nusantara" required autofocus />
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-4">
                                <label for="npsn" class="form-label">NPSN</label>
                                <input type="text" class="form-control" id="npsn" name="npsn" value="{{ old('npsn') }}" placeholder="Opsional" />
                            </div>
                            <div class="col-md-6 mb-4">
                                <label for="level" class="form-label">Jenjang</label>
                                <select class="form-select" id="level" name="level" required>
                                    <option value="">Pilih jenjang</option>
                                    @foreach (['PAUD', 'TK', 'SD', 'SMP', 'SMA', 'SMK', 'MA', 'PKBM'] as $level)
                                        <option value="{{ $level }}" @selected(old('level') === $level)>{{ $level }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="address" class="form-label">Alamat Sekolah</label>
                            <textarea class="form-control" id="address" name="address" rows="2" placeholder="Alamat lengkap sekolah">{{ old('address') }}</textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-4">
                                <label for="school_phone" class="form-label">Telepon Sekolah</label>
                                <input type="text" class="form-control" id="school_phone" name="school_phone" value="{{ old('school_phone') }}" placeholder="Opsional" />
                            </div>
                            <div class="col-md-6 mb-4">
                                <label for="school_email" class="form-label">Email Sekolah</label>
                                <input type="email" class="form-control" id="school_email" name="school_email" value="{{ old('school_email') }}" placeholder="Opsional" />
                            </div>
                        </div>

                        <h6 class="mb-3 mt-2">Data Admin Sekolah</h6>

                        <div class="mb-4">
                            <label for="admin_name" class="form-label">Nama Admin</label>
                            <input type="text" class="form-control" id="admin_name" name="admin_name" value="{{ old('admin_name') }}" placeholder="Nama penanggung jawab" required autocomplete="name" />
                        </div>

                        <div class="mb-4">
                            <label for="admin_email" class="form-label">Email Admin</label>
                            <input type="email" class="form-control" id="admin_email" name="admin_email" value="{{ old('admin_email') }}" placeholder="admin@sekolah.sch.id" required autocomplete="username" />
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-4 form-password-toggle">
                                <label class="form-label" for="password">Kata Sandi</label>
                                <div class="input-group input-group-merge">
                                    <input type="password" id="password" class="form-control" name="password" placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;" required autocomplete="new-password" />
                                    <span class="input-group-text cursor-pointer"><i class="bx bx-hide"></i></span>
                                </div>
                            </div>
                            <div class="col-md-6 mb-4 form-password-toggle">
                                <label class="form-label" for="password_confirmation">Konfirmasi Kata Sandi</label>
                                <div class="input-group input-group-merge">
                                    <input type="password" id="password_confirmation" class="form-control" name="password_confirmation" placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;" required autocomplete="new-password" />
                                    <span class="input-group-text cursor-pointer"><i class="bx bx-hide"></i></span>
                                </div>
                            </div>
                        </div>

                        <button class="btn btn-primary d-grid w-100">Kirim Pengajuan</button>
                    </form>

                    <p class="text-center">
                        <span>Sudah punya akun?</span>
                        <a href="{{ route('login') }}">
                            <span>Masuk</span>
                        </a>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <script src="{{ asset('assets/vendor/libs/jquery/jquery.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/popper/popper.js') }}"></script>
    <script src="{{ asset('assets/vendor/js/bootstrap.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js') }}"></script>
    <script src="{{ asset('assets/vendor/js/menu.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/@form-validation/popular.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/@form-validation/bootstrap5.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/@form-validation/auto-focus.js') }}"></script>
    <script src="{{ asset('assets/js/main.js') }}"></script>
    <script src="{{ asset('assets/js/pages-auth.js') }}"></script>
</body>

</html>
