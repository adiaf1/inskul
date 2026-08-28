@php
    $pwaShortcutSchool = $school ?? \App\Support\EffectiveAccess::school(request());
    $pwaShortcutEnabled = ! $pwaShortcutSchool
        || ($pwaShortcutSchool->status === 'active' && $pwaShortcutSchool->pwa_shortcut_enabled);
@endphp

@if($pwaShortcutEnabled)
    <button type="button" class="btn btn-label-success" data-pwa-install>
        <i class="bx bx-mobile-alt me-1"></i> Pasang di HP
    </button>
@endif
