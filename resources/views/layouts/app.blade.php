<!doctype html>

<html lang="en" class="layout-navbar-fixed layout-menu-fixed layout-compact" dir="ltr" data-skin="default"
    data-assets-path="../../assets/" data-template="horizontal-menu-template" data-bs-theme="light">

<head>
    <meta charset="utf-8" />
    <meta name="viewport"
        content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />

    <title>{{ config('app.name', 'Inskul') }}</title>

    <meta name="description" content="" />
    <meta name="theme-color" content="#696cff" />
    <meta name="apple-mobile-web-app-capable" content="yes" />
    <meta name="apple-mobile-web-app-status-bar-style" content="default" />

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('assets/img/branding/logo.png') }}?v=inskul-20260630" />

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
        href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&display=swap"
        rel="stylesheet" />

    <link rel="stylesheet" href="{{ asset('assets/vendor/fonts/iconify-icons.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/fonts/boxicons.css') }}" />

    <!-- Core CSS -->
    <!-- build:css assets/vendor/css/theme.css  -->

    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/pickr/pickr-themes.css') }}" />

    <link rel="stylesheet" href="{{ asset('assets/vendor/css/core.css') }}" class="template-customizer-core-css" />
    <link rel="stylesheet" href="{{ asset('assets/css/demo.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/inskul-theme.css') }}?v=20260804" />
    <link rel="stylesheet" href="{{ asset('assets/css/inskul-mobile.css') }}?v=20260803" />

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
    @php($moduleExams = \App\Support\ModuleAccess::enabled($activeSchool, 'exams'))
    @php($moduleDailyAttendance = \App\Support\ModuleAccess::enabled($activeSchool, 'daily_attendance'))
    @php($moduleClassAttendance = \App\Support\ModuleAccess::enabled($activeSchool, 'class_attendance'))
    @php($moduleScheduleAttendance = \App\Support\ModuleAccess::enabled($activeSchool, 'schedule_attendance'))
    @php($moduleTeacherAttendance = \App\Support\ModuleAccess::enabled($activeSchool, 'teacher_attendance'))
    @php($moduleAnyAttendance = \App\Support\ModuleAccess::anyEnabled($activeSchool, ['daily_attendance', 'class_attendance', 'schedule_attendance', 'teacher_attendance']))
    <!-- Layout wrapper -->
    <div class="layout-wrapper layout-navbar-full layout-horizontal layout-without-menu">
        <div class="layout-container">
            <!-- Navbar -->

            <nav class="layout-navbar navbar navbar-expand-xl align-items-center" id="layout-navbar">
                <div class="container-xxl">
                    <div class="navbar-brand app-brand demo d-none d-xl-flex py-0 me-4">
                        <a href="{{ url('/dashboard') }}" class="app-brand-link gap-2">
                            <span class="app-brand-logo demo">
                                <img src="{{ asset('assets/img/branding/logo.png') }}" alt="{{ config('app.name', 'Inskul') }}" style="height: 34px; width: auto;">
                            </span>
                            <span class="app-brand-text demo menu-text fw-bold text-heading">{{ config('app.name', 'Inskul') }}</span>
                        </a>

                        <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto d-xl-none">
                            <i class="icon-base bx bx-chevron-left d-flex align-items-center justify-content-center"></i>
                        </a>
                    </div>

                    <div class="navbar-nav align-items-xl-center me-3 me-xl-0 d-xl-none">
                        <a class="nav-item nav-link mobile-menu-trigger px-0 me-xl-6" href="javascript:void(0)"
                            data-bs-toggle="offcanvas" data-bs-target="#mobileMenu" aria-controls="mobileMenu"
                            aria-label="Buka menu">
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
                    @else
                        <a href="{{ route('dashboard') }}" class="mobile-brand d-xl-none me-auto">
                            <img src="{{ asset('assets/img/branding/logo.png') }}" alt="{{ config('app.name', 'Inskul') }}">
                            <span>{{ config('app.name', 'Inskul') }}</span>
                        </a>
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
                                                <a href="{{ route('view-as.index', [], false) }}" class="stretched-link">Mode Lihat</a>
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

                                @if(in_array($effectiveRole, ['school_admin', 'teacher'], true) && $moduleAnyAttendance)
                                <li class="menu-item {{ request()->is('attendances*') || request()->is('teacher-attendances*') ? 'active' : '' }}">
                                    <a href="javascript:void(0)" class="menu-link menu-toggle">
                                        <i class="menu-icon tf-icons bx bx-calendar-check"></i>
                                        <div data-i18n="Presensi">Presensi</div>
                                    </a>
                                    <ul class="menu-sub">
                                        @if($moduleDailyAttendance)
                                        <li class="menu-item {{ request()->is('attendances/check*') ? 'active' : '' }}">
                                            <a href="{{ route('attendances.check') }}" class="menu-link">
                                                <i class="menu-icon tf-icons bx bx-qr-scan"></i>
                                                <div data-i18n="Presensi Harian">Presensi Harian</div>
                                            </a>
                                        </li>
                                        @endif
                                        @if($effectiveRole === 'school_admin' && $moduleDailyAttendance)
                                        <li class="menu-item {{ request()->is('attendances/daily-dashboard*') ? 'active' : '' }}">
                                            <a href="{{ route('attendances.daily-dashboard') }}" class="menu-link">
                                                <i class="menu-icon tf-icons bx bx-bar-chart-alt-2"></i>
                                                <div data-i18n="Grafik Presensi Harian">Grafik Presensi Harian</div>
                                            </a>
                                        </li>
                                        @endif
                                        @if($moduleClassAttendance)
                                        <li class="menu-item {{ (request()->is('attendances/daily') || request()->is('attendances/daily/*')) ? 'active' : '' }}">
                                            <a href="{{ route('attendances.daily') }}" class="menu-link">
                                                <i class="menu-icon tf-icons bx bx-calendar-check"></i>
                                                <div data-i18n="Presensi Per Kelas">Presensi Per Kelas</div>
                                            </a>
                                        </li>
                                        @endif
                                        @if($moduleScheduleAttendance)
                                        <li class="menu-item {{ request()->is('attendances/schedules*') ? 'active' : '' }}">
                                            <a href="{{ route('attendances.schedule') }}" class="menu-link">
                                                <i class="menu-icon tf-icons bx bx-time-five"></i>
                                                <div data-i18n="Presensi Per Jadwal">Presensi Per Jadwal</div>
                                            </a>
                                        </li>
                                        @endif
                                        @if($effectiveRole === 'teacher' && $moduleTeacherAttendance)
                                        <li class="menu-item {{ request()->is('teacher-attendances') ? 'active' : '' }}">
                                            <a href="{{ route('teacher-attendances.index') }}" class="menu-link">
                                                <i class="menu-icon tf-icons bx bx-camera"></i>
                                                <div data-i18n="Presensi Guru">Presensi Guru</div>
                                            </a>
                                        </li>
                                        @endif
                                        @if($effectiveRole === 'school_admin' && $moduleTeacherAttendance)
                                        <li class="menu-item {{ request()->is('teacher-attendances/report*') ? 'active' : '' }}">
                                            <a href="{{ route('teacher-attendances.report') }}" class="menu-link">
                                                <i class="menu-icon tf-icons bx bx-user-check"></i>
                                                <div data-i18n="Laporan Presensi Guru">Laporan Presensi Guru</div>
                                            </a>
                                        </li>
                                        @endif
                                        @if($moduleClassAttendance)
                                        <li class="menu-item {{ request()->is('attendances/reports/daily*') ? 'active' : '' }}">
                                            <a href="{{ route('attendances.report.daily') }}" class="menu-link">
                                                <i class="menu-icon tf-icons bx bx-file"></i>
                                                <div data-i18n="Report Presensi Per Kelas">Report Presensi Per Kelas</div>
                                            </a>
                                        </li>
                                        @endif
                                        @if($moduleScheduleAttendance)
                                        <li class="menu-item {{ request()->is('attendances/reports/schedules*') ? 'active' : '' }}">
                                            <a href="{{ route('attendances.report.schedule') }}" class="menu-link">
                                                <i class="menu-icon tf-icons bx bx-file"></i>
                                                <div data-i18n="Report Presensi Per Jadwal">Report Presensi Per Jadwal</div>
                                            </a>
                                        </li>
                                        @endif
                                    </ul>
                                </li>
                                @endif

                                @if(in_array($effectiveRole, ['principal', 'parent'], true) && $moduleDailyAttendance)
                                <li class="menu-item {{ request()->is('attendances/check*') ? 'active' : '' }}">
                                    <a href="{{ route('attendances.check') }}" class="menu-link">
                                        <i class="menu-icon tf-icons bx bx-qr-scan"></i>
                                        <div data-i18n="Presensi">Presensi</div>
                                    </a>
                                </li>
                                @endif

                                @if($effectiveRole === 'principal' && $moduleDailyAttendance)
                                <li class="menu-item {{ request()->is('attendances/daily-dashboard*') ? 'active' : '' }}">
                                    <a href="{{ route('attendances.daily-dashboard') }}" class="menu-link">
                                        <i class="menu-icon tf-icons bx bx-bar-chart-alt-2"></i>
                                        <div data-i18n="Grafik Presensi">Grafik Presensi</div>
                                    </a>
                                </li>
                                @endif

                                @if($effectiveRole === 'principal' && $moduleTeacherAttendance)
                                <li class="menu-item {{ request()->is('teacher-attendances/report*') ? 'active' : '' }}">
                                    <a href="{{ route('teacher-attendances.report') }}" class="menu-link">
                                        <i class="menu-icon tf-icons bx bx-user-check"></i>
                                        <div data-i18n="Presensi Guru">Presensi Guru</div>
                                    </a>
                                </li>
                                @endif

                                @if(in_array($effectiveRole, ['school_admin', 'teacher'], true) && $moduleExams)
                                <li class="menu-item {{ request()->is('exams*') ? 'active' : '' }}">
                                    <a href="{{ route('exams.index') }}" class="menu-link">
                                        <i class="menu-icon tf-icons bx bx-task"></i>
                                        <div data-i18n="Ujian">Ujian</div>
                                    </a>
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
                                @if($moduleExams)
                                <li class="menu-item {{ request()->is('exams*') ? 'active' : '' }}">
                                    <a href="{{ route('exams.index') }}" class="menu-link">
                                        <i class="menu-icon tf-icons bx bx-task"></i>
                                        <div data-i18n="Ujian">Ujian</div>
                                    </a>
                                </li>
                                @endif
                                <li class="menu-item {{ request()->is('student-nametag') ? 'active' : '' }}">
                                    <a href="{{ route('students.own-nametag') }}" target="_blank" class="menu-link">
                                        <i class="menu-icon tf-icons bx bx-id-card"></i>
                                        <div data-i18n="Nametag">Nametag</div>
                                    </a>
                                </li>
                                @endif

                                @if($effectiveRole === 'school_admin')
                                <li class="menu-item {{ request()->is('academic-years*','semesters*','academic-calendar-events*','school-classes*','classrooms*','rooms*','schedules*','subjects*','teachers*','students*') ? 'active' : '' }}">
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
                                        <li class="menu-item {{ request()->is('academic-calendar-events*') ? 'active' : '' }}">
                                            <a href="{{ route('academic-calendar-events.index') }}" class="menu-link">
                                                <i class="menu-icon tf-icons bx bx-calendar-star"></i>
                                                <div data-i18n="Kalender Akademik">Kalender Akademik</div>
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
                                <form method="POST" action="{{ route('view-as.destroy', [], false) }}" class="m-0">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-label-danger" type="submit">Keluar Mode</button>
                                </form>
                            </div>
                        </div>
                    @endif
                    @yield('content')
                    @auth
                    <nav class="mobile-bottom-nav d-xl-none" aria-label="Navigasi utama mobile">
                        @if($effectiveRole === 'super_admin')
                            <a href="{{ route('dashboard') }}" class="mobile-bottom-nav__item {{ request()->is('dashboard') ? 'active' : '' }}">
                                <i class="bx bx-home-smile"></i><span>Home</span>
                            </a>
                            <a href="{{ route('schools.index') }}" class="mobile-bottom-nav__item {{ request()->is('schools*') ? 'active' : '' }}">
                                <i class="bx bx-building-house"></i><span>Sekolah</span>
                            </a>
                            <a href="{{ route('users.index') }}" class="mobile-bottom-nav__item {{ request()->is('users*') ? 'active' : '' }}">
                                <i class="bx bx-user"></i><span>User</span>
                            </a>
                            <a href="{{ route('view-as.index', [], false) }}" class="mobile-bottom-nav__item {{ request()->is('view-as*') ? 'active' : '' }}">
                                <i class="bx bx-show"></i><span>View</span>
                            </a>
                            <a href="{{ route('profile.edit') }}" class="mobile-bottom-nav__item {{ request()->is('profile') ? 'active' : '' }}">
                                <i class="bx bx-cog"></i><span>Akun</span>
                            </a>
                        @elseif($effectiveRole === 'school_admin')
                            <a href="{{ route('dashboard') }}" class="mobile-bottom-nav__item {{ request()->is('dashboard') ? 'active' : '' }}">
                                <i class="bx bx-home-smile"></i><span>Home</span>
                            </a>
                            <button type="button" class="mobile-bottom-nav__item {{ request()->is('academic-years*','semesters*','school-classes*','classrooms*','rooms*','schedules*','subjects*','teachers*','students*','school-users*','school-profile*') ? 'active' : '' }}"
                                data-bs-toggle="offcanvas" data-bs-target="#mobileMenu" aria-controls="mobileMenu">
                                <i class="bx bx-grid-alt"></i><span>Data</span>
                            </button>
                            @if($moduleAnyAttendance)
                                <a href="{{ $moduleDailyAttendance ? route('attendances.check') : ($moduleTeacherAttendance ? route('teacher-attendances.report') : route('attendances.index')) }}" class="mobile-bottom-nav__item {{ request()->is('attendances*') || request()->is('teacher-attendances*') ? 'active' : '' }}">
                                    <i class="bx bx-calendar-check"></i><span>Presensi</span>
                                </a>
                            @endif
                            @if($moduleExams)
                                <a href="{{ route('exams.index') }}" class="mobile-bottom-nav__item {{ request()->is('exams*') ? 'active' : '' }}">
                                    <i class="bx bx-task"></i><span>Ujian</span>
                                </a>
                            @endif
                            <a href="{{ route('profile.edit') }}" class="mobile-bottom-nav__item {{ request()->is('profile') ? 'active' : '' }}">
                                <i class="bx bx-cog"></i><span>Akun</span>
                            </a>
                        @elseif($effectiveRole === 'teacher')
                            <a href="{{ route('dashboard') }}" class="mobile-bottom-nav__item {{ request()->is('dashboard') ? 'active' : '' }}">
                                <i class="bx bx-home-smile"></i><span>Home</span>
                            </a>
                            <a href="{{ route('teacher-schedules.index') }}" class="mobile-bottom-nav__item {{ request()->is('teacher-schedules*') ? 'active' : '' }}">
                                <i class="bx bx-calendar-event"></i><span>Jadwal</span>
                            </a>
                            @if($moduleDailyAttendance)
                                <a href="{{ route('attendances.check') }}" class="mobile-bottom-nav__item {{ request()->is('attendances/check*') ? 'active' : '' }}">
                                    <i class="bx bx-qr-scan"></i><span>Siswa</span>
                                </a>
                            @endif
                            @if($moduleTeacherAttendance)
                                <a href="{{ route('teacher-attendances.index') }}" class="mobile-bottom-nav__item {{ request()->is('teacher-attendances') ? 'active' : '' }}">
                                    <i class="bx bx-camera"></i><span>Guru</span>
                                </a>
                            @elseif($moduleAnyAttendance)
                                <a href="{{ route('attendances.index') }}" class="mobile-bottom-nav__item {{ request()->is('attendances*') ? 'active' : '' }}">
                                    <i class="bx bx-calendar-check"></i><span>Presensi</span>
                                </a>
                            @endif
                            @if($moduleExams)
                                <a href="{{ route('exams.index') }}" class="mobile-bottom-nav__item {{ request()->is('exams*') ? 'active' : '' }}">
                                    <i class="bx bx-task"></i><span>Ujian</span>
                                </a>
                            @endif
                            <a href="{{ route('profile.edit') }}" class="mobile-bottom-nav__item {{ request()->is('profile') ? 'active' : '' }}">
                                <i class="bx bx-cog"></i><span>Akun</span>
                            </a>
                        @elseif($effectiveRole === 'student')
                            <a href="{{ route('dashboard') }}" class="mobile-bottom-nav__item {{ request()->is('dashboard') ? 'active' : '' }}">
                                <i class="bx bx-home-smile"></i><span>Home</span>
                            </a>
                            <a href="{{ route('student-schedules.index') }}" class="mobile-bottom-nav__item {{ request()->is('student-schedules*') ? 'active' : '' }}">
                                <i class="bx bx-calendar-event"></i><span>Jadwal</span>
                            </a>
                            @if($moduleExams)
                                <a href="{{ route('exams.index') }}" class="mobile-bottom-nav__item {{ request()->is('exams*') ? 'active' : '' }}">
                                    <i class="bx bx-task"></i><span>Ujian</span>
                                </a>
                            @endif
                            <a href="{{ route('students.own-nametag') }}" target="_blank" class="mobile-bottom-nav__item {{ request()->is('student-nametag') ? 'active' : '' }}">
                                <i class="bx bx-id-card"></i><span>Kartu</span>
                            </a>
                            <a href="{{ route('profile.edit') }}" class="mobile-bottom-nav__item {{ request()->is('profile') ? 'active' : '' }}">
                                <i class="bx bx-cog"></i><span>Akun</span>
                            </a>
                        @else
                            <a href="{{ route('dashboard') }}" class="mobile-bottom-nav__item {{ request()->is('dashboard') ? 'active' : '' }}">
                                <i class="bx bx-home-smile"></i><span>Home</span>
                            </a>
                            @if(in_array($effectiveRole, ['principal', 'parent'], true) && $moduleDailyAttendance)
                                <a href="{{ route('attendances.check') }}" class="mobile-bottom-nav__item {{ request()->is('attendances*') ? 'active' : '' }}">
                                    <i class="bx bx-qr-scan"></i><span>Presensi</span>
                                </a>
                            @endif
                            <a href="{{ route('profile.edit') }}" class="mobile-bottom-nav__item {{ request()->is('profile') ? 'active' : '' }}">
                                <i class="bx bx-cog"></i><span>Akun</span>
                            </a>
                        @endif
                    </nav>
                    @endauth
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
                                    , {{ config('app.name', 'Inskul') }} by adiaf.my.id
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
    @auth
    <div class="offcanvas offcanvas-start mobile-app-drawer d-xl-none" tabindex="-1" id="mobileMenu"
        aria-labelledby="mobileMenuLabel">
        <div class="offcanvas-header">
            <div class="d-flex align-items-center gap-2 min-w-0">
                <span class="mobile-drawer-logo">
                    <img src="{{ asset('assets/img/branding/logo.png') }}" alt="{{ config('app.name', 'Inskul') }}">
                </span>
                <div class="min-w-0">
                    <h5 class="offcanvas-title text-truncate mb-0" id="mobileMenuLabel">{{ config('app.name', 'Inskul') }}</h5>
                    <small class="text-muted text-truncate d-block">{{ $activeSchool?->name ?? ($authUser?->name ?? 'Menu') }}</small>
                </div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Tutup"></button>
        </div>
        <div class="offcanvas-body">
            <div class="mobile-drawer-section">
                <a href="{{ route('dashboard') }}" class="mobile-drawer-link {{ request()->is('dashboard') ? 'active' : '' }}">
                    <i class="bx bx-home-smile"></i><span>Dashboard</span>
                </a>

                @if($effectiveRole === 'super_admin')
                    <a href="{{ route('schools.index') }}" class="mobile-drawer-link {{ request()->is('schools*') ? 'active' : '' }}">
                        <i class="bx bx-building-house"></i><span>Registrasi Sekolah</span>
                    </a>
                    <a href="{{ route('users.index') }}" class="mobile-drawer-link {{ request()->is('users*') ? 'active' : '' }}">
                        <i class="bx bx-user"></i><span>Pengguna</span>
                    </a>
                    <a href="{{ route('view-as.index', [], false) }}" class="mobile-drawer-link {{ request()->is('view-as*') ? 'active' : '' }}">
                        <i class="bx bx-show"></i><span>Mode Lihat</span>
                    </a>
                @endif

                @if($effectiveRole === 'school_admin')
                    <a href="{{ route('school-profile.edit') }}" class="mobile-drawer-link {{ request()->is('school-profile') ? 'active' : '' }}">
                        <i class="bx bx-building-house"></i><span>Profil Sekolah</span>
                    </a>
                    <a href="{{ route('school-users.index') }}" class="mobile-drawer-link {{ request()->is('school-users') ? 'active' : '' }}">
                        <i class="bx bx-user-plus"></i><span>Pengguna Sekolah</span>
                    </a>
                @endif

                @if(in_array($effectiveRole, ['school_admin', 'teacher', 'principal', 'parent'], true) && $moduleDailyAttendance)
                    <a href="{{ route('attendances.check') }}" class="mobile-drawer-link {{ request()->is('attendances/check*') ? 'active' : '' }}">
                        <i class="bx bx-qr-scan"></i><span>Presensi Harian</span>
                    </a>
                @endif

                @if($effectiveRole === 'teacher' && $moduleTeacherAttendance)
                    <a href="{{ route('teacher-attendances.index') }}" class="mobile-drawer-link {{ request()->is('teacher-attendances') ? 'active' : '' }}">
                        <i class="bx bx-camera"></i><span>Presensi Guru</span>
                    </a>
                @endif

                @if(in_array($effectiveRole, ['school_admin', 'principal'], true) && $moduleTeacherAttendance)
                    <a href="{{ route('teacher-attendances.report') }}" class="mobile-drawer-link {{ request()->is('teacher-attendances/report*') ? 'active' : '' }}">
                        <i class="bx bx-user-check"></i><span>Laporan Presensi Guru</span>
                    </a>
                @endif

                @if(in_array($effectiveRole, ['school_admin', 'principal'], true) && $moduleDailyAttendance)
                    <a href="{{ route('attendances.daily-dashboard') }}" class="mobile-drawer-link {{ request()->is('attendances/daily-dashboard*') ? 'active' : '' }}">
                        <i class="bx bx-bar-chart-alt-2"></i><span>Grafik Presensi Harian</span>
                    </a>
                @endif

                @if(in_array($effectiveRole, ['school_admin', 'teacher'], true))
                    @if($moduleClassAttendance)
                    <a href="{{ route('attendances.daily') }}" class="mobile-drawer-link {{ (request()->is('attendances/daily') || request()->is('attendances/daily/*')) ? 'active' : '' }}">
                        <i class="bx bx-calendar-check"></i><span>Presensi Per Kelas</span>
                    </a>
                    @endif
                    @if($moduleScheduleAttendance)
                    <a href="{{ route('attendances.schedule') }}" class="mobile-drawer-link {{ request()->is('attendances/schedules*') ? 'active' : '' }}">
                        <i class="bx bx-time-five"></i><span>Presensi Per Jadwal</span>
                    </a>
                    @endif
                    @if($moduleClassAttendance)
                    <a href="{{ route('attendances.report.daily') }}" class="mobile-drawer-link {{ request()->is('attendances/reports/daily*') ? 'active' : '' }}">
                        <i class="bx bx-file"></i><span>Laporan Per Kelas</span>
                    </a>
                    @endif
                    @if($moduleScheduleAttendance)
                    <a href="{{ route('attendances.report.schedule') }}" class="mobile-drawer-link {{ request()->is('attendances/reports/schedules*') ? 'active' : '' }}">
                        <i class="bx bx-file"></i><span>Laporan Per Jadwal</span>
                    </a>
                    @endif
                @endif

                @if(in_array($effectiveRole, ['school_admin', 'teacher', 'student'], true) && $moduleExams)
                    <a href="{{ route('exams.index') }}" class="mobile-drawer-link {{ request()->is('exams*') ? 'active' : '' }}">
                        <i class="bx bx-task"></i><span>Ujian</span>
                    </a>
                @endif

                @if($effectiveRole === 'teacher')
                    <a href="{{ route('teacher-schedules.index') }}" class="mobile-drawer-link {{ request()->is('teacher-schedules*') ? 'active' : '' }}">
                        <i class="bx bx-calendar-event"></i><span>Jadwal Mengajar</span>
                    </a>
                @endif

                @if($effectiveRole === 'student')
                    <a href="{{ route('student-schedules.index') }}" class="mobile-drawer-link {{ request()->is('student-schedules*') ? 'active' : '' }}">
                        <i class="bx bx-calendar-event"></i><span>Jadwal Pelajaran</span>
                    </a>
                    <a href="{{ route('students.own-nametag') }}" target="_blank" class="mobile-drawer-link {{ request()->is('student-nametag') ? 'active' : '' }}">
                        <i class="bx bx-id-card"></i><span>Nametag</span>
                    </a>
                @endif
            </div>

            @if($effectiveRole === 'school_admin')
            <div class="mobile-drawer-heading">Data Akademik</div>
            <div class="mobile-drawer-grid">
                <a href="{{ route('academic-years.index') }}" class="{{ request()->is('academic-years*') ? 'active' : '' }}"><i class="bx bx-calendar"></i><span>Tahun Ajaran</span></a>
                <a href="{{ route('semesters.index') }}" class="{{ request()->is('semesters*') ? 'active' : '' }}"><i class="bx bx-calendar-check"></i><span>Semester</span></a>
                <a href="{{ route('academic-calendar-events.index') }}" class="{{ request()->is('academic-calendar-events*') ? 'active' : '' }}"><i class="bx bx-calendar-star"></i><span>Kalender</span></a>
                <a href="{{ route('school-classes.index') }}" class="{{ request()->is('school-classes*') ? 'active' : '' }}"><i class="bx bx-building"></i><span>Kelas</span></a>
                <a href="{{ route('classrooms.index') }}" class="{{ request()->is('classrooms*') ? 'active' : '' }}"><i class="bx bx-group"></i><span>Rombel</span></a>
                <a href="{{ route('rooms.index') }}" class="{{ request()->is('rooms*') ? 'active' : '' }}"><i class="bx bx-door-open"></i><span>Ruangan</span></a>
                <a href="{{ route('schedules.index') }}" class="{{ request()->is('schedules*') ? 'active' : '' }}"><i class="bx bx-calendar-event"></i><span>Jadwal</span></a>
                <a href="{{ route('subjects.index') }}" class="{{ request()->is('subjects*') ? 'active' : '' }}"><i class="bx bx-book"></i><span>Mapel</span></a>
                <a href="{{ route('teachers.index') }}" class="{{ request()->is('teachers*') ? 'active' : '' }}"><i class="bx bx-chalkboard"></i><span>Guru</span></a>
                <a href="{{ route('students.index') }}" class="{{ request()->is('students*') ? 'active' : '' }}"><i class="bx bx-user"></i><span>Siswa</span></a>
            </div>
            @endif

            <div class="mobile-drawer-section mt-4">
                <a href="{{ route('profile.edit') }}" class="mobile-drawer-link {{ request()->is('profile') ? 'active' : '' }}">
                    <i class="bx bx-cog"></i><span>Profil Saya</span>
                </a>
                <button type="button" class="mobile-drawer-link text-danger" onclick="confirmLogout()">
                    <i class="bx bx-power-off"></i><span>Keluar</span>
                </button>
            </div>
        </div>
    </div>
    @endauth

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
