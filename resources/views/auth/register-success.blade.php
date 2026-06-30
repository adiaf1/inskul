<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="light-style layout-wide customizer-hide" dir="ltr" data-theme="theme-default" data-assets-path="{{ asset('assets/') }}/" data-template="horizontal-menu-template" data-style="light">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />
    <title>Registrasi Terkirim - {{ config('app.name') }}</title>
    <link rel="icon" type="image/png" href="{{ asset('assets/img/branding/logo.png') }}?v=inskul-20260630" />
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/css/rtl/core.css') }}" class="template-customizer-core-css" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/css/rtl/theme-default.css') }}" class="template-customizer-theme-css" />
    <link rel="stylesheet" href="{{ asset('assets/css/demo.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/css/pages/page-auth.css') }}" />
    <script src="{{ asset('assets/vendor/js/helpers.js') }}"></script>
    <script src="{{ asset('assets/js/config.js') }}"></script>
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
                    <img src="{{ asset('assets/img/illustrations/boy-with-rocket-light.png') }}" class="img-fluid" alt="Registrasi terkirim" width="700" />
                </div>
            </div>

            <div class="d-flex col-12 col-lg-5 col-xl-4 align-items-center authentication-bg p-sm-12 p-6">
                <div class="w-px-400 mx-auto mt-12 pt-5">
                    <div class="mb-4">
                        <span class="badge bg-label-warning">Menunggu Approval</span>
                    </div>
                    <h4 class="mb-2">Registrasi sekolah berhasil dikirim</h4>
                    <p class="mb-6">
                        Akun admin sekolah belum aktif. Super Admin akan meninjau pengajuan sekolah terlebih dahulu.
                    </p>

                    <a href="{{ route('login') }}" class="btn btn-primary d-grid w-100 mb-3">Ke Halaman Masuk</a>
                    <a href="{{ route('home') }}" class="btn btn-label-secondary d-grid w-100">Kembali ke Beranda</a>
                </div>
            </div>
        </div>
    </div>

    <script src="{{ asset('assets/vendor/libs/jquery/jquery.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/popper/popper.js') }}"></script>
    <script src="{{ asset('assets/vendor/js/bootstrap.js') }}"></script>
    <script src="{{ asset('assets/js/main.js') }}"></script>
</body>

</html>
