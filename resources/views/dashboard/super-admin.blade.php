@extends('layouts.app')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="py-4 mb-6">Dashboard Super Admin</h4>

    <div class="card">
        <div class="card-header">
            Super Admin
        </div>
        <div class="card-body">
            <h5 class="card-title">Selamat datang, {{ Auth::user()->name }}!</h5>
            <p class="card-text">
                Anda login sebagai {{ Auth::user()->roles->pluck('name')->join(', ') }}.
            </p>
            <p class="card-text text-muted">
                Area ini akan dipakai untuk mengelola registrasi sekolah, approval sekolah, dan konfigurasi sistem.
            </p>
        </div>
    </div>
</div>
@endsection
