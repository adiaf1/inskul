<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Inskul') }}</title>
    <link rel="icon" type="image/png" href="{{ asset('assets/img/branding/logo.png') }}?v=inskul-20260630">
    <style>
        :root {
            color-scheme: light;
            font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            line-height: 1.5;
            color: #172033;
            background: #f6f8fb;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
        }

        .page {
            min-height: 100vh;
            display: grid;
            grid-template-rows: auto 1fr auto;
        }

        .topbar {
            width: min(1120px, calc(100% - 32px));
            margin: 0 auto;
            padding: 22px 0;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 10px;
            color: #111827;
            font-size: 18px;
            font-weight: 700;
            text-decoration: none;
        }

        .brand-mark {
            width: 34px;
            height: 34px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        .brand-mark img {
            width: auto;
            height: 34px;
            object-fit: contain;
            display: block;
        }

        .nav {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .button {
            min-height: 40px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0 16px;
            border-radius: 8px;
            border: 1px solid #d7deea;
            color: #172033;
            background: #ffffff;
            font-size: 14px;
            font-weight: 700;
            text-decoration: none;
            transition: 160ms ease;
        }

        .button:hover {
            border-color: #9db2d4;
        }

        .button-primary {
            border-color: #2563eb;
            color: #ffffff;
            background: #2563eb;
        }

        .button-primary:hover {
            border-color: #1d4ed8;
            background: #1d4ed8;
        }

        .hero {
            width: min(1120px, calc(100% - 32px));
            margin: 0 auto;
            padding: 72px 0 56px;
            display: grid;
            grid-template-columns: minmax(0, 1.1fr) minmax(320px, 0.9fr);
            gap: 42px;
            align-items: center;
        }

        .eyebrow {
            margin: 0 0 14px;
            color: #2563eb;
            font-size: 13px;
            font-weight: 800;
            letter-spacing: 0;
            text-transform: uppercase;
        }

        h1 {
            max-width: 720px;
            margin: 0;
            color: #0f172a;
            font-size: clamp(38px, 6vw, 68px);
            line-height: 1.02;
            letter-spacing: 0;
        }

        .lead {
            max-width: 650px;
            margin: 22px 0 0;
            color: #475569;
            font-size: 18px;
        }

        .actions {
            margin-top: 32px;
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
        }

        .panel {
            border: 1px solid #dfe6f1;
            border-radius: 8px;
            background: #ffffff;
            box-shadow: 0 24px 80px rgba(15, 23, 42, 0.08);
            overflow: hidden;
        }

        .panel-head {
            padding: 18px 20px;
            border-bottom: 1px solid #e6edf6;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
        }

        .panel-title {
            margin: 0;
            color: #111827;
            font-size: 15px;
            font-weight: 800;
        }

        .status {
            padding: 5px 9px;
            border-radius: 999px;
            color: #166534;
            background: #dcfce7;
            font-size: 12px;
            font-weight: 800;
        }

        .steps {
            margin: 0;
            padding: 6px 20px 20px;
            list-style: none;
        }

        .step {
            padding: 16px 0;
            border-bottom: 1px solid #edf2f7;
        }

        .step:last-child {
            border-bottom: 0;
        }

        .step strong {
            display: block;
            color: #111827;
            font-size: 14px;
        }

        .step span {
            display: block;
            margin-top: 3px;
            color: #64748b;
            font-size: 13px;
        }

        .footer {
            width: min(1120px, calc(100% - 32px));
            margin: 0 auto;
            padding: 24px 0;
            color: #64748b;
            font-size: 13px;
        }

        @media (max-width: 820px) {
            .topbar {
                align-items: flex-start;
                flex-direction: column;
            }

            .hero {
                padding-top: 36px;
                grid-template-columns: 1fr;
            }

            .nav {
                width: 100%;
            }

            .nav .button {
                flex: 1;
            }
        }
    </style>
</head>
<body>
    <div class="page">
        <header class="topbar">
            <a class="brand" href="{{ route('home') }}">
                <span class="brand-mark">
                    <img src="{{ asset('assets/img/branding/logo.png') }}" alt="{{ config('app.name', 'Inskul') }}">
                </span>
                <span>{{ config('app.name', 'Inskul') }}</span>
            </a>

            <nav class="nav" aria-label="Navigasi utama">
                @auth
                    <a class="button button-primary" href="{{ route('dashboard') }}">Dashboard</a>
                @else
                    <a class="button" href="{{ route('login') }}">Masuk</a>
                    @if (Route::has('register'))
                        <a class="button button-primary" href="{{ route('register') }}">Registrasi Sekolah</a>
                    @endif
                @endauth
            </nav>
        </header>

        <main class="hero">
            <section>
                @if(session('error'))
                    <div style="margin-bottom: 18px; padding: 12px 14px; border: 1px solid #fecaca; border-radius: 8px; color: #991b1b; background: #fef2f2; font-size: 14px; font-weight: 700;">
                        {{ session('error') }}
                    </div>
                @endif
                <p class="eyebrow">Sistem sekolah multi-sekolah</p>
                <h1>Kelola sekolah, presensi, dan ujian dari satu platform.</h1>
                <p class="lead">
                    Pondasi awal disiapkan untuk banyak sekolah, banyak peran, dan modul yang bisa berkembang bertahap dari data akademik sampai layanan orang tua.
                </p>
                <div class="actions">
                    @auth
                        <a class="button button-primary" href="{{ route('dashboard') }}">Buka Dashboard</a>
                    @else
                        <a class="button button-primary" href="{{ route('login') }}">Masuk ke Sistem</a>
                        @if (Route::has('register'))
                            <a class="button" href="{{ route('register') }}">Registrasi Sekolah</a>
                        @endif
                    @endauth
                </div>
            </section>

            <aside class="panel" aria-label="Tahap pengembangan">
                <div class="panel-head">
                    <p class="panel-title">Tahap Awal</p>
                    <span class="status">Fondasi</span>
                </div>
                <ul class="steps">
                    <li class="step">
                        <strong>Role pengguna</strong>
                        <span>Super admin, admin sekolah, guru, siswa, dan orang tua.</span>
                    </li>
                    <li class="step">
                        <strong>Registrasi sekolah</strong>
                        <span>Calon sekolah mengajukan data, super admin melakukan approval.</span>
                    </li>
                    <li class="step">
                        <strong>Onboarding sekolah</strong>
                        <span>Admin sekolah menyiapkan tahun ajaran, kelas, guru, siswa, dan mapel.</span>
                    </li>
                </ul>
            </aside>
        </main>

        <footer class="footer">
            {{ config('app.name', 'Inskul') }} siap dikembangkan bertahap untuk modul sekolah berikutnya.
        </footer>
    </div>
</body>
</html>
