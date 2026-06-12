@extends('layouts.app')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    @if($errors->any())
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                Swal.fire({
                    title: 'Error!',
                    text: '{{ $errors->first() }}',
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
            });
        </script>
    @endif

    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 py-4 mb-2">
        <div>
            <h4 class="mb-1">Edit Rombel</h4>
            <p class="text-muted mb-0">{{ $school->name }} - perbarui rombel {{ $classroom->name }}.</p>
        </div>

        <a href="{{ route('classrooms.index') }}" class="btn btn-label-secondary">
            Kembali
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('classrooms.update', $classroom) }}">
                @csrf
                @method('PUT')

                @include('classrooms.partials.form', [
                    'mode' => 'edit_'.$classroom->id,
                    'classroom' => $classroom,
                    'selectedStudents' => $selectedStudents,
                ])

                <div class="d-flex flex-wrap gap-2">
                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                    <a href="{{ route('classrooms.index') }}" class="btn btn-label-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
