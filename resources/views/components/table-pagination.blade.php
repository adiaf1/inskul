@props([
    'paginator',
    'label' => 'data',
])

<div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mt-4">
    <div class="text-muted small">
        Menampilkan {{ $paginator->firstItem() ?? 0 }} sampai {{ $paginator->lastItem() ?? 0 }} dari {{ $paginator->total() }} {{ $label }}
    </div>

    @if($paginator->hasPages())
        <div class="d-flex justify-content-md-end">
            {{ $paginator->onEachSide(1)->links() }}
        </div>
    @endif
</div>
