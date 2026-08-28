@extends('layouts.app')

@section('content')


<div class="container-xxl flex-grow-1 container-p-y">
    <!-- Toast untuk notifikasi -->
<div class="bs-toast toast toast-ex animate__animated my-2 fade {{ session('success') ? 'bg-success' : ($errors->any() ? 'bg-danger' : 'bg-primary') }} animate__bounceInDown show ">
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
            document.getElementById('toast-message').innerText = '{{ session('success') }}';
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
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 py-4 mb-2">
        <h4 class="mb-0">Dashboard</h4>
        <div class="d-flex flex-wrap gap-2">
            @include('dashboard.partials.pwa-install-button')
        </div>
    </div>
    <div class="card">
        <div class="card-header">
            Dashboard
        </div>
        <div class="card-body">
            <h5 class="card-title">Welcome, {{ Auth::user()->name }}!</h5>
            <p class="card-text">You are logged in as
                {{ Auth::user()->roles->pluck('name')->join(', ') }}.
            </p>

            
        </div>
    </div>
</div>



@endsection
