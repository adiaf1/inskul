<!doctype html>

<html lang="en" class="layout-navbar-fixed layout-menu-fixed layout-compact" dir="ltr" data-skin="default"
    data-assets-path="../../assets/" data-template="horizontal-menu-template" data-bs-theme="light">

<head>
    <meta charset="utf-8" />
    <meta name="viewport"
        content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />

    <title>{{ config('app.name', 'Laravel') }}</title>

    <meta name="description" content="" />

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="{{ asset('assets/img/favicon/favicon.ico') }}" />

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
        href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&display=swap"
        rel="stylesheet" />

    <link rel="stylesheet" href="{{ asset('assets/vendor/fonts/iconify-icons.css') }}" />

    <!-- Core CSS -->
    <!-- build:css assets/vendor/css/theme.css  -->

    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/pickr/pickr-themes.css') }}" />

    <link rel="stylesheet" href="{{ asset('assets/vendor/css/core.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/demo.css') }}" />

    <!-- Vendors CSS -->

    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css') }}" />

    <!-- endbuild -->

    <link rel="stylesheet" href="{{ asset('assets/vendor/fonts/flag-icons.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/apex-charts/apex-charts.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/notyf/notyf.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/animate-css/animate.css') }}" />

    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/select2/select2.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/tagify/tagify.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/bootstrap-select/bootstrap-select.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/typeahead-js/typeahead.css') }}" />

     <!-- endbuild -->

    <!-- Page CSS -->

    <!-- Helpers -->
    <script src="{{ asset('assets/vendor/js/helpers.js') }}"></script>
    <!--! Template customizer & Theme config files MUST be included after core stylesheets and helpers.js in the <head> section -->

    <!--? Template customizer: To hide customizer set displayCustomizer value false in config.js.  -->
    <script src="{{ asset('assets/vendor/js/template-customizer.js') }}"></script>

    <!--? Config:  Mandatory theme config file contain global vars & default theme options, Set your preferred theme option in this file.  -->

    <script src="{{ asset('assets/js/config.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="{{ asset('assets/vendor/libs/tagify/tagify.js') }}"></script>

</head>

<body>
    @php($authUser = Auth::user())
    @php($effectiveRole = \App\Support\EffectiveAccess::role(request()))
    @php($activeSchool = \App\Support\EffectiveAccess::school(request()))
    @php($viewAs = \App\Support\EffectiveAccess::payload(request()))
    @php($pendingSchoolCount = $authUser?->hasRole('super_admin') ? \App\Models\School::where('status', 'pending')->count() : 0)
    <!-- Layout wrapper -->
    <div class="layout-wrapper layout-navbar-full layout-horizontal layout-without-menu">
        <div class="layout-container">
            <!-- Navbar -->

            <nav class="layout-navbar navbar navbar-expand-xl align-items-center" id="layout-navbar">
                <div class="container-xxl">
                    <div class="navbar-brand app-brand demo d-none d-xl-flex py-0 me-4">
                        <a href="{{ url('/dashboard') }}" class="app-brand-link gap-2">
                            <span class="app-brand-logo demo">
                                <span class="text-primary">
                                    <svg width="25" viewBox="0 0 25 42" version="1.1" xmlns="http://www.w3.org/2000/svg"
                                        xmlns:xlink="http://www.w3.org/1999/xlink">
                                        <defs>
                                            <path
                                                d="M13.7918663,0.358365126 L3.39788168,7.44174259 C0.566865006,9.69408886 -0.379795268,12.4788597 0.557900856,15.7960551 C0.68998853,16.2305145 1.09562888,17.7872135 3.12357076,19.2293357 C3.8146334,19.7207684 5.32369333,20.3834223 7.65075054,21.2172976 L7.59773219,21.2525164 L2.63468769,24.5493413 C0.445452254,26.3002124 0.0884951797,28.5083815 1.56381646,31.1738486 C2.83770406,32.8170431 5.20850219,33.2640127 7.09180128,32.5391577 C8.347334,32.0559211 11.4559176,30.0011079 16.4175519,26.3747182 C18.0338572,24.4997857 18.6973423,22.4544883 18.4080071,20.2388261 C17.963753,17.5346866 16.1776345,15.5799961 13.0496516,14.3747546 L10.9194936,13.4715819 L18.6192054,7.984237 L13.7918663,0.358365126 Z"
                                                id="path-1"></path>
                                            <path
                                                d="M5.47320593,6.00457225 C4.05321814,8.216144 4.36334763,10.0722806 6.40359441,11.5729822 C8.61520715,12.571656 10.0999176,13.2171421 10.8577257,13.5094407 L15.5088241,14.433041 L18.6192054,7.984237 C15.5364148,3.11535317 13.9273018,0.573395879 13.7918663,0.358365126 C13.5790555,0.511491653 10.8061687,2.3935607 5.47320593,6.00457225 Z"
                                                id="path-3"></path>
                                            <path
                                                d="M7.50063644,21.2294429 L12.3234468,23.3159332 C14.1688022,24.7579751 14.397098,26.4880487 13.008334,28.506154 C11.6195701,30.5242593 10.3099883,31.790241 9.07958868,32.3040991 C5.78142938,33.4346997 4.13234973,34 4.13234973,34 C4.13234973,34 2.75489982,33.0538207 2.37032616e-14,31.1614621 C-0.55822714,27.8186216 -0.55822714,26.0572515 -4.05231404e-15,25.8773518 C0.83734071,25.6075023 2.77988457,22.8248993 3.3049379,22.52991 C3.65497346,22.3332504 5.05353963,21.8997614 7.50063644,21.2294429 Z"
                                                id="path-4"></path>
                                            <path
                                                d="M20.6,7.13333333 L25.6,13.8 C26.2627417,14.6836556 26.0836556,15.9372583 25.2,16.6 C24.8538077,16.8596443 24.4327404,17 24,17 L14,17 C12.8954305,17 12,16.1045695 12,15 C12,14.5672596 12.1403557,14.1461923 12.4,13.8 L17.4,7.13333333 C18.0627417,6.24967773 19.3163444,6.07059163 20.2,6.73333333 C20.3516113,6.84704183 20.4862915,6.981722 20.6,7.13333333 Z"
                                                id="path-5"></path>
                                        </defs>
                                        <g id="g-app-brand" stroke="none" stroke-width="1" fill="none"
                                            fill-rule="evenodd">
                                            <g id="Brand-Logo" transform="translate(-27.000000, -15.000000)">
                                                <g id="Icon" transform="translate(27.000000, 15.000000)">
                                                    <g id="Mask" transform="translate(0.000000, 8.000000)">
                                                        <mask id="mask-2" fill="white">
                                                            <use xlink:href="#path-1"></use>
                                                        </mask>
                                                        <use fill="currentColor" xlink:href="#path-1"></use>
                                                        <g id="Path-3" mask="url(#mask-2)">
                                                            <use fill="currentColor" xlink:href="#path-3"></use>
                                                            <use fill-opacity="0.2" fill="#FFFFFF" xlink:href="#path-3">
                                                            </use>
                                                        </g>
                                                        <g id="Path-4" mask="url(#mask-2)">
                                                            <use fill="currentColor" xlink:href="#path-4"></use>
                                                            <use fill-opacity="0.2" fill="#FFFFFF" xlink:href="#path-4">
                                                            </use>
                                                        </g>
                                                    </g>
                                                    <g id="Triangle"
                                                        transform="translate(19.000000, 11.000000) rotate(-300.000000) translate(-19.000000, -11.000000) ">
                                                        <use fill="currentColor" xlink:href="#path-5"></use>
                                                        <use fill-opacity="0.2" fill="#FFFFFF" xlink:href="#path-5">
                                                        </use>
                                                    </g>
                                                </g>
                                            </g>
                                        </g>
                                    </svg>
                                </span>
                            </span>
                            <span class="app-brand-text demo menu-text fw-bold text-heading">{{ config('app.name', 'Laravel') }}</span>
                        </a>

                        <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto d-xl-none">
                            <i class="icon-base bx bx-chevron-left d-flex align-items-center justify-content-center"></i>
                        </a>
                    </div>

                    <div class="layout-menu-toggle navbar-nav align-items-xl-center me-4 me-xl-0 d-xl-none">
                        <a class="nav-item nav-link px-0 me-xl-6" href="javascript:void(0)">
                            <i class="icon-base bx bx-menu icon-md"></i>
                        </a>
                    </div>

                    @if($activeSchool)
                        <div class="d-flex align-items-center gap-2 min-w-0 me-auto">
                            <span class="avatar avatar-sm rounded border bg-white d-flex align-items-center justify-content-center flex-shrink-0">
                                @if($activeSchool->logo_path)
                                    <img src="{{ \App\Support\SchoolFileStorage::url($activeSchool->logo_path) }}" alt="Logo {{ $activeSchool->name }}" class="rounded" style="width: 100%; height: 100%; object-fit: contain;">
                                @else
                                    <i class="bx bx-building-house text-primary"></i>
                                @endif
                            </span>
                            <span class="d-flex flex-column min-w-0">
                                <small class="text-muted lh-1 d-none d-sm-block">Sekolah aktif</small>
                                <span class="fw-semibold text-heading text-truncate" style="max-width: min(34vw, 360px);">
                                    {{ $activeSchool->name }}
                                </span>
                            </span>
                        </div>
                    @endif

                    <div class="navbar-nav-right d-flex align-items-center justify-content-end" id="navbar-collapse">
                        <ul class="navbar-nav flex-row align-items-center ms-md-auto">
                            @hasanyrole('super_admin')
                            <li class="nav-item dropdown-notifications navbar-dropdown dropdown me-3 me-xl-2">
                                <a class="nav-link dropdown-toggle hide-arrow position-relative" href="javascript:void(0);"
                                    data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false"
                                    title="Notifikasi registrasi sekolah">
                                    <i class="icon-base bx bx-bell icon-md"></i>
                                    @if($pendingSchoolCount > 0)
                                        <span class="badge rounded-pill bg-danger badge-dot badge-notifications border"></span>
                                    @endif
                                </a>
                                <ul class="dropdown-menu dropdown-menu-end p-0">
                                    <li class="dropdown-menu-header border-bottom">
                                        <div class="dropdown-header d-flex align-items-center py-3">
                                            <h6 class="mb-0 me-auto">Notifikasi</h6>
                                            @if($pendingSchoolCount > 0)
                                                <span class="badge bg-label-primary">{{ $pendingSchoolCount }} baru</span>
                                            @endif
                                        </div>
                                    </li>
                                    <li>
                                        <a class="dropdown-item d-flex align-items-start gap-3 py-3" href="{{ route('schools.index') }}">
                                            <span class="avatar rounded bg-label-warning">
                                                <i class="bx bx-building-house"></i>
                                            </span>
                                            <span class="flex-grow-1">
                                                <span class="d-block fw-medium">Registrasi Sekolah</span>
                                                <small class="text-muted">
                                                    @if($pendingSchoolCount > 0)
                                                        {{ $pendingSchoolCount }} sekolah menunggu approve.
                                                    @else
                                                        Tidak ada sekolah yang menunggu approve.
                                                    @endif
                                                </small>
                                            </span>
                                        </a>
                                    </li>
                                </ul>
                            </li>
                            @endhasanyrole

                            <!-- Style Switcher -->
                            <li class="nav-item dropdown me-2 me-xl-0">
                                <a class="nav-link dropdown-toggle hide-arrow" id="nav-theme" href="javascript:void(0);"
                                    data-bs-toggle="dropdown">
                                    <i class="icon-base bx bx-sun icon-md theme-icon-active"></i>
                                    <span class="d-none ms-2" id="nav-theme-text">Ubah tema</span>
                                </a>
                                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="nav-theme-text">
                                    <li>
                                        <button type="button" class="dropdown-item align-items-center active"
                                            data-bs-theme-value="light" aria-pressed="false">
                                            <span><i class="icon-base bx bx-sun icon-md me-3"
                                                    data-icon="sun"></i>Terang</span>
                                        </button>
                                    </li>
                                    <li>
                                        <button type="button" class="dropdown-item align-items-center"
                                            data-bs-theme-value="dark" aria-pressed="true">
                                            <span><i class="icon-base bx bx-moon icon-md me-3"
                                                    data-icon="moon"></i>Gelap</span>
                                        </button>
                                    </li>
                                    <li>
                                        <button type="button" class="dropdown-item align-items-center"
                                            data-bs-theme-value="system" aria-pressed="false">
                                            <span><i class="icon-base bx bx-desktop icon-md me-3"
                                                    data-icon="desktop"></i>Sistem</span>
                                        </button>
                                    </li>
                                </ul>
                            </li>
                            <!-- / Style Switcher-->

                            @hasanyrole('super_admin')
                            <!-- Quick links  -->
                            <li class="nav-item dropdown-shortcuts navbar-dropdown dropdown me-2 me-xl-0">
                                <a class="nav-link dropdown-toggle hide-arrow" href="javascript:void(0);"
                                    data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false">
                                    <i class="icon-base bx bx-grid-alt icon-md"></i>
                                </a>
                                <div class="dropdown-menu dropdown-menu-end p-0">
                                    <div class="dropdown-menu-header border-bottom">
                                        <div class="dropdown-header d-flex align-items-center py-3">
                                            <h6 class="mb-0 me-auto">Pintasan</h6>
                                            <a href="javascript:void(0)" class="dropdown-shortcuts-add py-2"
                                                data-bs-toggle="tooltip" data-bs-placement="top"
                                                title="Tambah pintasan"><i
                                                    class="icon-base bx bx-plus-circle text-heading"></i></a>
                                        </div>
                                    </div>
                                    <div class="dropdown-shortcuts-list scrollable-container">
                                        <div class="row row-bordered overflow-visible g-0">
                                            <div class="dropdown-shortcuts-item col">
                                                <span class="dropdown-shortcuts-icon rounded-circle mb-3">
                                                    <i class="icon-base bx bx-building icon-26px text-heading"></i>
                                                </span>
                                                <a href="{{ route('schools.index') }}" class="stretched-link">Registrasi Sekolah</a>
                                                <small>Kelola persetujuan sekolah</small>
                                            </div>
                                            <div class="dropdown-shortcuts-item col">
                                                <span class="dropdown-shortcuts-icon rounded-circle mb-3">
                                                    <i class="icon-base bx bx-user icon-26px text-heading"></i>
                                                </span>
                                                <a href="{{ route('users.index') }}" class="stretched-link">Pengguna</a>
                                                <small>Kelola akun sistem</small>
                                            </div>
                                        </div>
                                        <div class="row row-bordered overflow-visible g-0">
                                            <div class="dropdown-shortcuts-item col">
                                                <span class="dropdown-shortcuts-icon rounded-circle mb-3">
                                                    <i class="icon-base bx bx-show icon-26px text-heading"></i>
                                                </span>
                                                <a href="{{ route('view-as.index') }}" class="stretched-link">Mode Lihat</a>
                                                <small>Lihat sebagai sekolah</small>
                                            </div>
                                            <div class="dropdown-shortcuts-item col">
                                                <span class="dropdown-shortcuts-icon rounded-circle mb-3">
                                                    <i class="icon-base bx bx-home-smile icon-26px text-heading"></i>
                                                </span>
                                                <a href="{{ route('dashboard') }}" class="stretched-link">Dashboard</a>
                                                <small>Ringkasan sistem</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </li>
                            <!-- Quick links -->
                            @endhasanyrole

                            
                            <!-- User -->
                            <li class="nav-item navbar-dropdown dropdown-user dropdown">
                                <a class="nav-link dropdown-toggle hide-arrow p-0" href="javascript:void(0);"
                                    data-bs-toggle="dropdown">
                                    <div class="avatar avatar-online">
                                    <img src="{{ \App\Support\SchoolFileStorage::url($authUser?->profile_picture) }}" alt="Profile Picture">
                                    </div>
                                </a>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li>
                                        <a class="dropdown-item" href="{{ route('profile.edit') }}">
                                            <div class="d-flex">
                                                <div class="flex-shrink-0 me-3">
                                                  <div class="avatar avatar-online">
                                                    <img src="{{ \App\Support\SchoolFileStorage::url($authUser?->profile_picture) }}" alt="" class="w-px-40 h-auto rounded-circle">
                                                  </div>
                                                </div>
                                                <div class="flex-grow-1">
                                                  <h6 class="mb-0">{{ auth()->user()->name }} </h6>
                                                  <small class="text-muted">{{ implode(', ', auth()->user()->roles->pluck('name')->toArray()) }}</small>
                                                </div>
                                              </div>
                                        </a>
                                    </li>
                                    @if($activeSchool)
                                        <li>
                                            <div class="dropdown-divider my-1"></div>
                                        </li>
                                        <li>
                                            <div class="dropdown-item">
                                                <div class="d-flex align-items-center">
                                                    <div class="flex-shrink-0 me-3">
                                                        <span class="avatar avatar-sm rounded border bg-white d-flex align-items-center justify-content-center">
                                                            @if($activeSchool->logo_path)
                                                                <img src="{{ \App\Support\SchoolFileStorage::url($activeSchool->logo_path) }}" alt="Logo {{ $activeSchool->name }}" class="rounded" style="width: 100%; height: 100%; object-fit: contain;">
                                                            @else
                                                                <i class="bx bx-building-house text-primary"></i>
                                                            @endif
                                                        </span>
                                                    </div>
                                                    <div class="flex-grow-1 min-w-0">
                                                        <small class="text-muted d-block">Sekolah aktif</small>
                                                        <span class="fw-medium text-truncate d-block">{{ $activeSchool->name }}</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </li>
                                    @endif
                                    <li>
                                        <div class="dropdown-divider my-1"></div>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="{{ route('profile.edit') }}">
                                            <i class="bx bx-user bx-md me-3"></i><span>Profil Saya</span>
                                        </a>
                                    </li>
                                    <li>
                                        <div class="dropdown-divider my-1"></div>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="javascript:void(0);" onclick="confirmLogout()">
                                            <i class="bx bx-power-off bx-md me-3"></i><span>Keluar</span>
                                        </a>
                                    </li>

                                    <script>
                                      function confirmLogout() {
                                          Swal.fire({
                                              title: 'Anda yakin?',
                                              text: "Anda akan keluar dari sistem.",
                                              icon: 'warning',
                                              showCancelButton: true,
                                              confirmButtonColor: '#d33',
                                              cancelButtonColor: '#3085d6',
                                              confirmButtonText: 'Ya, keluar!'
                                          }).then((result) => {
                                              if (result.isConfirmed) {
                                                  // Log out with the form submission
                                                  document.getElementById('logout-form').submit();
                                              }
                                          });
                                      }
                                      </script>
                                      
                                      <!-- Form untuk logout -->
                                      <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                                          @csrf
                                      </form>
                                </ul>
                            </li>
                            <!--/ User -->
                        </ul>
                    </div>
                </div>
            </nav>

            <!-- / Navbar -->

            <!-- Layout container -->
            <div class="layout-page">
                <!-- Content wrapper -->
                <div class="content-wrapper">
                    <!-- Menu -->
                    <aside id="layout-menu" class="layout-menu-horizontal menu-horizontal menu flex-grow-0">
                        <div class="container-xxl d-flex h-100">
                            <ul class="menu-inner">
                                <!-- Dashboards -->
                                <li
                                    class="menu-item {{ request()->is('dashboard') ? 'active' : '' }}">
                                    <a href="{{ url('/dashboard') }}" class="menu-link">
                                        <i class="menu-icon tf-icons bx bx-home-smile"></i>
                                        <div data-i18n="Dashboard">Dashboard</div>
                                    </a>
                                </li>

                                @if($effectiveRole === 'school_admin')
                                <li class="menu-item {{ request()->is('school-profile') ? 'active' : '' }}">
                                    <a href="{{ route('school-profile.edit') }}" class="menu-link">
                                        <i class="menu-icon tf-icons bx bx-building-house"></i>
                                        <div data-i18n="Profil Sekolah">Profil Sekolah</div>
                                    </a>
                                </li>
                                @endif

                                @if(in_array($effectiveRole, ['school_admin', 'teacher'], true))
                                <li class="menu-item {{ request()->is('attendances*') ? 'active' : '' }}">
                                    <a href="javascript:void(0)" class="menu-link menu-toggle">
                                        <i class="menu-icon tf-icons bx bx-calendar-check"></i>
                                        <div data-i18n="Presensi">Presensi</div>
                                    </a>
                                    <ul class="menu-sub">
                                        <li class="menu-item {{ request()->is('attendances/daily*') ? 'active' : '' }}">
                                            <a href="{{ route('attendances.daily') }}" class="menu-link">
                                                <i class="menu-icon tf-icons bx bx-calendar-check"></i>
                                                <div data-i18n="Presensi Harian">Presensi Harian</div>
                                            </a>
                                        </li>
                                        <li class="menu-item {{ request()->is('attendances/schedules*') ? 'active' : '' }}">
                                            <a href="{{ route('attendances.schedule') }}" class="menu-link">
                                                <i class="menu-icon tf-icons bx bx-time-five"></i>
                                                <div data-i18n="Presensi Per Jadwal">Presensi Per Jadwal</div>
                                            </a>
                                        </li>
                                        <li class="menu-item {{ request()->is('attendances/reports/daily*') ? 'active' : '' }}">
                                            <a href="{{ route('attendances.report.daily') }}" class="menu-link">
                                                <i class="menu-icon tf-icons bx bx-file"></i>
                                                <div data-i18n="Report Presensi Harian">Report Presensi Harian</div>
                                            </a>
                                        </li>
                                        <li class="menu-item {{ request()->is('attendances/reports/schedules*') ? 'active' : '' }}">
                                            <a href="{{ route('attendances.report.schedule') }}" class="menu-link">
                                                <i class="menu-icon tf-icons bx bx-file"></i>
                                                <div data-i18n="Report Presensi Per Jadwal">Report Presensi Per Jadwal</div>
                                            </a>
                                        </li>
                                    </ul>
                                </li>
                                @endif

                                @if($effectiveRole === 'teacher')
                                <li class="menu-item {{ request()->is('teacher-schedules*') ? 'active' : '' }}">
                                    <a href="{{ route('teacher-schedules.index') }}" class="menu-link">
                                        <i class="menu-icon tf-icons bx bx-calendar-event"></i>
                                        <div data-i18n="Jadwal">Jadwal</div>
                                    </a>
                                </li>
                                @endif

                                @if($effectiveRole === 'student')
                                <li class="menu-item {{ request()->is('student-schedules*') ? 'active' : '' }}">
                                    <a href="{{ route('student-schedules.index') }}" class="menu-link">
                                        <i class="menu-icon tf-icons bx bx-calendar-event"></i>
                                        <div data-i18n="Jadwal">Jadwal</div>
                                    </a>
                                </li>
                                <li class="menu-item {{ request()->is('student-nametag') ? 'active' : '' }}">
                                    <a href="{{ route('students.own-nametag') }}" target="_blank" class="menu-link">
                                        <i class="menu-icon tf-icons bx bx-id-card"></i>
                                        <div data-i18n="Nametag">Nametag</div>
                                    </a>
                                </li>
                                @endif

                                @if($effectiveRole === 'school_admin')
                                <li class="menu-item {{ request()->is('academic-years*','semesters*','school-classes*','classrooms*','rooms*','schedules*','subjects*','teachers*','students*') ? 'active' : '' }}">
                                    <a href="javascript:void(0)" class="menu-link menu-toggle">
                                        <i class="menu-icon tf-icons bx bx-book-open"></i>
                                        <div data-i18n="Data Akademik">Data Akademik</div>
                                    </a>
                                    <ul class="menu-sub">
                                        <li class="menu-item {{ request()->is('academic-years*') ? 'active' : '' }}">
                                            <a href="{{ route('academic-years.index') }}" class="menu-link">
                                                <i class="menu-icon tf-icons bx bx-calendar"></i>
                                                <div data-i18n="Tahun Ajaran">Tahun Ajaran</div>
                                            </a>
                                        </li>
                                        <li class="menu-item {{ request()->is('semesters*') ? 'active' : '' }}">
                                            <a href="{{ route('semesters.index') }}" class="menu-link">
                                                <i class="menu-icon tf-icons bx bx-calendar-check"></i>
                                                <div data-i18n="Semester">Semester</div>
                                            </a>
                                        </li>
                                        <li class="menu-item {{ request()->is('school-classes*') ? 'active' : '' }}">
                                            <a href="{{ route('school-classes.index') }}" class="menu-link">
                                                <i class="menu-icon tf-icons bx bx-building"></i>
                                                <div data-i18n="Kelas">Kelas</div>
                                            </a>
                                        </li>
                                        <li class="menu-item {{ request()->is('classrooms*') ? 'active' : '' }}">
                                            <a href="{{ route('classrooms.index') }}" class="menu-link">
                                                <i class="menu-icon tf-icons bx bx-group"></i>
                                                <div data-i18n="Rombel">Rombel</div>
                                            </a>
                                        </li>
                                        <li class="menu-item {{ request()->is('rooms*') ? 'active' : '' }}">
                                            <a href="{{ route('rooms.index') }}" class="menu-link">
                                                <i class="menu-icon tf-icons bx bx-door-open"></i>
                                                <div data-i18n="Ruangan">Ruangan</div>
                                            </a>
                                        </li>
                                        <li class="menu-item {{ request()->is('schedules*') ? 'active' : '' }}">
                                            <a href="{{ route('schedules.index') }}" class="menu-link">
                                                <i class="menu-icon tf-icons bx bx-calendar-event"></i>
                                                <div data-i18n="Jadwal">Jadwal</div>
                                            </a>
                                        </li>
                                        <li class="menu-item {{ request()->is('subjects*') ? 'active' : '' }}">
                                            <a href="{{ route('subjects.index') }}" class="menu-link">
                                                <i class="menu-icon tf-icons bx bx-book"></i>
                                                <div data-i18n="Mata Pelajaran">Mata Pelajaran</div>
                                            </a>
                                        </li>
                                        <li class="menu-item {{ request()->is('teachers*') ? 'active' : '' }}">
                                            <a href="{{ route('teachers.index') }}" class="menu-link">
                                                <i class="menu-icon tf-icons bx bx-chalkboard"></i>
                                                <div data-i18n="Guru">Guru</div>
                                            </a>
                                        </li>
                                        <li class="menu-item {{ request()->is('students*') ? 'active' : '' }}">
                                            <a href="{{ route('students.index') }}" class="menu-link">
                                                <i class="menu-icon tf-icons bx bx-user"></i>
                                                <div data-i18n="Murid">Murid</div>
                                            </a>
                                        </li>
                                    </ul>
                                </li>
                                <li class="menu-item {{ request()->is('school-users') ? 'active' : '' }}">
                                    <a href="{{ route('school-users.index') }}" class="menu-link">
                                        <i class="menu-icon tf-icons bx bx-user-plus"></i>
                                        <div data-i18n="Pengguna Sekolah">Pengguna Sekolah</div>
                                    </a>
                                </li>
                                @endif

                            </ul>
                        </div>
                    </aside>
                    <!-- / Menu -->

                    <!-- Content -->
                    @if(($viewAs['active'] ?? false) === true)
                        <div class="container-xxl pt-3">
                            <div class="alert alert-warning d-flex flex-wrap align-items-center justify-content-between gap-3 mb-0">
                                <div>
                                    <strong>Mode Lihat Sebagai:</strong>
                                    {{ $viewAs['role_label'] ?? '-' }} di {{ $viewAs['school_name'] ?? '-' }}
                                    @if(! empty($viewAs['user_name']))
                                        sebagai {{ $viewAs['user_name'] }}
                                    @endif
                                </div>
                                <form method="POST" action="{{ route('view-as.destroy') }}" class="m-0">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-label-danger" type="submit">Keluar Mode</button>
                                </form>
                            </div>
                        </div>
                    @endif
                    @yield('content')
                    <!--/ Content -->

                    <!-- Footer -->
                    <footer class="content-footer footer bg-footer-theme">
                        <div class="container-xxl">
                            <div
                                class="footer-container d-flex align-items-center justify-content-between py-4 flex-md-row flex-column">
                                <div class="mb-2 mb-md-0">
                                    ©
                                    <script>
                                        document.write(new Date().getFullYear());

                                    </script>
                                    , {{ config('app.name', 'Laravel') }} ❤️ by
                                    <a href="" 
                                        class="footer-link">PT. Megakarya Teknologi Nusantara</a>
                                </div>
                                
                            </div>
                        </div>
                    </footer>
                    <!-- / Footer -->

                    <div class="content-backdrop fade"></div>
                </div>
                <!--/ Content wrapper -->
            </div>

            <!--/ Layout container -->
        </div>
    </div>

    <!-- Overlay -->
    <div class="layout-overlay layout-menu-toggle"></div>

    <!-- Drag Target Area To SlideIn Menu On Small Screens -->
    <div class="drag-target"></div>

    <!--/ Layout wrapper -->

    <!-- Core JS -->
    <!-- build:js assets/vendor/js/theme.js  -->

    <script src="{{ asset('assets/vendor/libs/jquery/jquery.js') }}"></script>

    <script src="{{ asset('assets/vendor/libs/popper/popper.js') }}"></script>
    <script src="{{ asset('assets/vendor/js/bootstrap.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/@algolia/autocomplete-js.js') }}"></script>

    <script src="{{ asset('assets/vendor/libs/pickr/pickr.js') }}"></script>

    <script src="{{ asset('assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js') }}">
    </script>

    <script src="{{ asset('assets/vendor/libs/hammer/hammer.js') }}"></script>

    <script src="{{ asset('assets/vendor/libs/i18n/i18n.js') }}"></script>

    <script src="{{ asset('assets/vendor/js/menu.js') }}"></script>

    <!-- endbuild -->

    <!-- Main JS -->

    <script src="{{ asset('assets/js/main.js') }}"></script>

    <!-- Page JS -->
    <script src="{{ asset('assets/js/dashboards-analytics.js') }}"></script>


    <!-- Vendors JS -->
    <script src="{{ asset('assets/vendor/libs/select2/select2.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/tagify/tagify.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/bootstrap-select/bootstrap-select.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/typeahead-js/typeahead.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/bloodhound/bloodhound.js') }}"></script>


    <!-- Page JS -->
    <script src="{{ asset('assets/js/forms-selects.js') }}"></script>
    <script src="{{ asset('assets/js/forms-tagify.js') }}"></script>
    <script src="{{ asset('assets/js/forms-typeahead.js') }}"></script>
    
</body>

</html>
