<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="light-style layout-wide customizer-hide" dir="ltr" data-theme="theme-default" data-assets-path="{{ asset('assets/') }}/" data-template="horizontal-menu-template" data-style="light">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />
    <title>Masuk - {{ config('app.name') }}</title>
    <meta name="description" content="" />
    <link rel="icon" type="image/png" href="{{ asset('assets/img/branding/logo.png') }}?v=inskul-20260630" />

    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet" />

    <link rel="stylesheet" href="{{ asset('assets/vendor/css/rtl/core.css') }}" class="template-customizer-core-css" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/css/rtl/theme-default.css') }}" class="template-customizer-theme-css" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/fonts/boxicons.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/demo.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/inskul-theme.css') }}?v=20260804" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/css/pages/page-auth.css') }}" />

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
            <div class="d-none d-lg-flex col-lg-7 col-xl-8 align-items-center p-5">
                <div class="w-100 d-flex justify-content-center">
                    <img src="{{ asset('assets/img/illustrations/boy-with-rocket-light.png') }}" class="img-fluid" alt="Ilustrasi masuk" width="700" />
                </div>
            </div>

            @if(session('success'))
                <script>
                    Swal.fire({
                        title: 'Sukses!',
                        text: '{{ session('success') }}',
                        icon: 'success',
                        confirmButtonText: 'OK'
                    });
                </script>
            @endif

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

            <div class="d-flex col-12 col-lg-5 col-xl-4 align-items-center authentication-bg p-sm-12 p-6">
                <div class="w-px-400 mx-auto mt-12 pt-5">
                    <div class="mb-6">
                        <img src="{{ asset('assets/img/branding/logo.png') }}" alt="{{ config('app.name') }}" style="height: 64px; width: auto;">
                    </div>
                    <h4 class="mb-1">Selamat Datang di {{ config('app.name') }}!</h4>
                    <p class="mb-6">Silakan masuk ke dalam sistem</p>

                    <form class="mb-6" action="{{ route('login') }}" method="POST">
                        @csrf
                        <div class="mb-6">
                            <label for="email" class="form-label">Email</label>
                            <input type="text" class="form-control" id="email" name="email" placeholder="Masukkan email" required autofocus />
                        </div>
                        <div class="mb-6 form-password-toggle">
                            <label class="form-label" for="password">Kata Sandi</label>
                            <div class="input-group input-group-merge">
                                <input type="password" id="password" class="form-control" name="password" placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;" required />
                                <span class="input-group-text cursor-pointer" aria-label="Tampilkan kata sandi">
                                    <i class="bx bx-hide"></i>
                                </span>
                            </div>
                        </div>
                        <div class="my-8">
                            <div class="d-flex justify-content-between">
                                <div class="form-check mb-0 ms-2">
                                    <input class="form-check-input" type="checkbox" id="remember-me" name="remember" />
                                    <label class="form-check-label" for="remember-me"> Ingat Saya </label>
                                </div>
                            </div>
                        </div>
                        <button class="btn btn-primary d-grid w-100">Masuk</button>
                    </form>
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
