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
            <h4 class="mb-1">Modul Sekolah</h4>
            <p class="text-muted mb-0">{{ $school->name }} - aktifkan atau nonaktifkan modul yang tersedia.</p>
        </div>
        <a href="{{ route('schools.index') }}" class="btn btn-label-secondary">
            <i class="bx bx-arrow-back me-1"></i> Kembali
        </a>
    </div>

    <form method="POST" action="{{ route('schools.modules.update', $school) }}">
        @csrf
        @method('PUT')

        <div class="card">
            <div class="card-body">
                <div class="row g-4">
                    @foreach($modules as $module)
                        @php
                            $schoolModule = $school->modules->firstWhere('id', $module->id);
                            $isEnabled = $schoolModule ? (bool) $schoolModule->pivot->is_enabled : true;
                        @endphp
                        <div class="col-md-6">
                            <div class="border rounded p-3 h-100">
                                <div class="d-flex align-items-start justify-content-between gap-3">
                                    <div>
                                        <h6 class="mb-1">{{ $module->name }}</h6>
                                        <div class="text-muted small">{{ $module->description }}</div>
                                        <code class="small">{{ $module->code }}</code>
                                    </div>
                                    <div class="form-check form-switch mb-0">
                                        <input class="form-check-input" type="checkbox" id="module_{{ $module->code }}" name="modules[]" value="{{ $module->code }}" @checked($isEnabled)>
                                        <label class="form-check-label" for="module_{{ $module->code }}">
                                            {{ $isEnabled ? 'Aktif' : 'Nonaktif' }}
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="bx bx-save me-1"></i> Simpan Modul
                    </button>
                    <a href="{{ route('schools.index') }}" class="btn btn-label-secondary">Batal</a>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection
