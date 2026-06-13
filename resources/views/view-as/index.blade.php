@extends('layouts.app')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    @if(session('success') || $errors->any())
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                Swal.fire({
                    title: '{{ session('success') ? 'Sukses!' : 'Error!' }}',
                    text: '{{ session('success') ?: $errors->first() }}',
                    icon: '{{ session('success') ? 'success' : 'error' }}',
                    confirmButtonText: 'OK'
                });
            });
        </script>
    @endif

    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 py-4 mb-2">
        <div>
            <h4 class="mb-1">Mode Lihat Sebagai</h4>
            <p class="text-muted mb-0">Pilih sekolah dan jenis akun untuk melihat sistem dari sudut pandang sekolah.</p>
        </div>
        @if(($viewAs['active'] ?? false) === true)
            <form method="POST" action="{{ route('view-as.destroy') }}">
                @csrf
                @method('DELETE')
                <button class="btn btn-label-danger" type="submit">
                    <i class="bx bx-x me-1"></i> Keluar Mode
                </button>
            </form>
        @endif
    </div>

    @if(($viewAs['active'] ?? false) === true)
        <div class="alert alert-warning">
            Mode aktif: <strong>{{ $viewAs['role_label'] ?? '-' }}</strong>
            di <strong>{{ $viewAs['school_name'] ?? '-' }}</strong>
            @if(! empty($viewAs['user_name']))
                sebagai <strong>{{ $viewAs['user_name'] }}</strong>
            @endif
        </div>
    @endif

    <div class="row g-4">
        <div class="col-lg-5">
            <div class="card">
                <div class="card-body">
                    <form method="GET" action="{{ route('view-as.index') }}">
                        <label class="form-label" for="school_id_filter">Sekolah</label>
                        <select class="form-select" id="school_id_filter" name="school_id" onchange="this.form.submit()">
                            <option value="">Pilih sekolah</option>
                            @foreach($schools as $school)
                                <option value="{{ $school->id }}" @selected((string) $selectedSchoolId === (string) $school->id)>
                                    {{ $school->name }}{{ $school->npsn ? ' - '.$school->npsn : '' }}
                                </option>
                            @endforeach
                        </select>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-7">
            <div class="card">
                <div class="card-body">
                    <form method="POST" action="{{ route('view-as.store') }}">
                        @csrf

                        <input type="hidden" name="school_id" value="{{ $selectedSchoolId }}">

                        <div class="mb-4">
                            <label class="form-label" for="role">Jenis Akun</label>
                            <select class="form-select" id="role" name="role" required>
                                <option value="">Pilih jenis akun</option>
                                @foreach($roles as $value => $label)
                                    <option value="{{ $value }}" @selected(old('role', $viewAs['role'] ?? '') === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-4">
                            <label class="form-label" for="user_id">User Spesifik</label>
                            <select class="form-select" id="user_id" name="user_id" @disabled(! $selectedSchoolId)>
                                <option value="">Tanpa user spesifik</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}" @selected(old('user_id', $viewAs['user_id'] ?? '') === $user->id)>
                                        {{ $user->name }} - {{ $user->roles->pluck('name')->implode(', ') ?: 'tanpa role' }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="form-text">Untuk guru atau murid, pilih user agar konteksnya lebih akurat.</div>
                        </div>

                        <button type="submit" class="btn btn-primary" @disabled(! $selectedSchoolId)>
                            Aktifkan Mode
                        </button>
                        <a href="{{ route('dashboard') }}" class="btn btn-label-secondary">Kembali</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
