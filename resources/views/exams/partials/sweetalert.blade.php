@if(session('success') || $errors->any())
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            Swal.fire({
                title: @json(session('success') ? 'Sukses!' : 'Error!'),
                text: @json(session('success') ?: $errors->first()),
                icon: @json(session('success') ? 'success' : 'error'),
                confirmButtonText: 'OK'
            });
        });
    </script>
@endif

<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.js-swal-confirm').forEach(function (form) {
            form.addEventListener('submit', function (event) {
                if (form.dataset.confirmed === 'true') {
                    return;
                }

                event.preventDefault();

                Swal.fire({
                    title: form.dataset.swalTitle || 'Anda yakin?',
                    text: form.dataset.swalText || 'Aksi ini akan diproses.',
                    icon: form.dataset.swalIcon || 'warning',
                    showCancelButton: true,
                    confirmButtonText: form.dataset.swalConfirm || 'Ya, lanjutkan',
                    cancelButtonText: form.dataset.swalCancel || 'Batal',
                    confirmButtonColor: '#696cff',
                    cancelButtonColor: '#8592a3'
                }).then(function (result) {
                    if (result.isConfirmed) {
                        form.dataset.confirmed = 'true';
                        form.submit();
                    }
                });
            });
        });
    });
</script>
