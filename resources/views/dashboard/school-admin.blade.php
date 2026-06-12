@extends('layouts.app')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="py-4 mb-6">Dashboard Admin Sekolah</h4>

    <div class="card">
        <div class="card-header">
            Admin Sekolah
        </div>
        <div class="card-body">
            <h5 class="card-title">Selamat datang, {{ Auth::user()->name }}!</h5>
            <p class="card-text">
                Anda login sebagai {{ Auth::user()->roles->pluck('name')->join(', ') }}.
            </p>
            @if($school)
                <div class="row g-3 mt-2">
                    <div class="col-md-3">
                        <div class="border rounded p-3">
                            <div class="text-muted small">Sekolah</div>
                            <strong>{{ $school->name }}</strong>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="border rounded p-3">
                            <div class="text-muted small">Tahun Ajaran</div>
                            <strong>{{ $school->academic_years_count }}</strong>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="border rounded p-3">
                            <div class="text-muted small">Kelas</div>
                            <strong>{{ $school->classes_count }}</strong>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="border rounded p-3">
                            <div class="text-muted small">Rombel</div>
                            <strong>{{ $school->classrooms_count }}</strong>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="border rounded p-3">
                            <div class="text-muted small">Jadwal</div>
                            <strong>{{ $school->schedules_count }}</strong>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="border rounded p-3">
                            <div class="text-muted small">Ruangan</div>
                            <strong>{{ $school->rooms_count }}</strong>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="border rounded p-3">
                            <div class="text-muted small">Mata Pelajaran</div>
                            <strong>{{ $school->subjects_count }}</strong>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="border rounded p-3">
                            <div class="text-muted small">Guru</div>
                            <strong>{{ $school->teachers_count }}</strong>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="border rounded p-3">
                            <div class="text-muted small">Murid</div>
                            <strong>{{ $school->students_count }}</strong>
                        </div>
                    </div>
                </div>
            @endif
            <div class="d-flex flex-wrap gap-2 mt-3">
                <a href="{{ route('school-profile.edit') }}" class="btn btn-label-primary">
                    Profil Sekolah
                </a>
                <a href="{{ route('school-users.index') }}" class="btn btn-primary">
                    Kelola Pengguna Sekolah
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
