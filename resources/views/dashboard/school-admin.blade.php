@extends('layouts.app')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="py-4 mb-6">Dashboard Admin Sekolah</h4>

    @if($school)
        @php
            $setupItems = [
                ['label' => 'Tahun Ajaran', 'count' => $school->academic_years_count, 'route' => route('academic-years.index')],
                ['label' => 'Semester', 'count' => $school->semesters_count, 'route' => route('semesters.index')],
                ['label' => 'Kelas', 'count' => $school->classes_count, 'route' => route('school-classes.index')],
                ['label' => 'Mata Pelajaran', 'count' => $school->subjects_count, 'route' => route('subjects.index')],
                ['label' => 'Ruangan', 'count' => $school->rooms_count, 'route' => route('rooms.index')],
                ['label' => 'Guru', 'count' => $school->teachers_count, 'route' => route('teachers.index')],
                ['label' => 'Murid', 'count' => $school->students_count, 'route' => route('students.index')],
                ['label' => 'Rombel', 'count' => $school->classrooms_count, 'route' => route('classrooms.index')],
                ['label' => 'Jadwal', 'count' => $school->schedules_count, 'route' => route('schedules.index')],
            ];
            $unfinishedSetup = collect($setupItems)->filter(fn ($item) => (int) $item['count'] === 0);
        @endphp

        @if($unfinishedSetup->isNotEmpty())
            <div class="alert alert-warning mb-4">
                <div class="d-flex flex-wrap align-items-start justify-content-between gap-3">
                    <div>
                        <h6 class="alert-heading mb-1">Setup akademik belum lengkap</h6>
                        <div>Lengkapi data dasar berikut agar modul presensi, rombel, dan jadwal bisa digunakan dengan baik.</div>
                    </div>
                    <span class="badge bg-label-warning">{{ $unfinishedSetup->count() }} belum selesai</span>
                </div>
            </div>
        @endif
    @endif

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

    @if($school)
        <div class="card mt-4">
            <div class="card-header">
                Checklist Setup Sekolah
            </div>
            <div class="card-body">
                <div class="row g-3">
                    @foreach($setupItems as $item)
                        <div class="col-md-4">
                            <div class="border rounded p-3 h-100 d-flex align-items-center justify-content-between gap-3">
                                <div>
                                    <div class="fw-semibold">{{ $item['label'] }}</div>
                                    <div class="text-muted small">{{ $item['count'] }} data</div>
                                </div>
                                @if((int) $item['count'] > 0)
                                    <span class="badge bg-label-success">Selesai</span>
                                @else
                                    <a href="{{ $item['route'] }}" class="btn btn-sm btn-label-primary">Isi Data</a>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif
</div>
@endsection
