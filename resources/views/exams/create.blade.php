@extends('layouts.app')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    @include('exams.partials.sweetalert')

    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 py-4 mb-2">
        <div>
            <h4 class="mb-1">Buat Ujian</h4>
            <p class="text-muted mb-0">{{ $school->name }} - ujian pilihan ganda 4 opsi.</p>
        </div>
        <a href="{{ route('exams.index') }}" class="btn btn-label-secondary">Kembali</a>
    </div>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('exams.store') }}">
                @csrf
                @include('exams.partials.form', ['exam' => null])

                <div class="mt-4">
                    <button class="btn btn-primary">Simpan Draft</button>
                    <a href="{{ route('exams.index') }}" class="btn btn-label-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
