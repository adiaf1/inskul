@extends('layouts.app')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 py-4 mb-2">
        <h4 class="mb-0">Dashboard Kepala Sekolah</h4>
        <div class="d-flex flex-wrap gap-2">
            @include('dashboard.partials.pwa-install-button')
        </div>
    </div>

    <div class="card">
        <div class="card-header">Kepala Sekolah</div>
        <div class="card-body">
            <h5 class="card-title">Selamat datang, {{ Auth::user()->name }}!</h5>
            <p class="card-text">
                Anda login sebagai {{ Auth::user()->roles->pluck('name')->join(', ') }}.
            </p>
        </div>
    </div>
</div>
@endsection
