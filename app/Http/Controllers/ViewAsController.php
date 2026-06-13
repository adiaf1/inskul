<?php

namespace App\Http\Controllers;

use App\Models\School;
use App\Models\User;
use App\Support\EffectiveAccess;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ViewAsController extends Controller
{
    private const ROLES = [
        'school_admin' => 'Admin Sekolah',
        'principal' => 'Kepala Sekolah',
        'teacher' => 'Guru',
        'student' => 'Murid',
        'parent' => 'Wali Murid',
    ];

    public function index(Request $request): View
    {
        $schools = School::query()
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        $selectedSchoolId = $request->input('school_id', $request->session()->get(EffectiveAccess::SESSION_KEY.'.school_id'));

        $users = collect();

        if ($selectedSchoolId) {
            $users = User::query()
                ->whereHas('schools', fn ($query) => $query->where('schools.id', $selectedSchoolId))
                ->with('roles')
                ->orderBy('name')
                ->get();
        }

        $roles = self::ROLES;
        $viewAs = EffectiveAccess::payload($request);

        return view('view-as.index', compact('schools', 'selectedSchoolId', 'users', 'roles', 'viewAs'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'school_id' => ['required', 'exists:schools,id'],
            'role' => ['required', 'in:'.implode(',', array_keys(self::ROLES))],
            'user_id' => ['nullable', 'exists:users,id'],
        ]);

        $school = School::query()
            ->whereKey($validated['school_id'])
            ->where('status', 'active')
            ->firstOrFail();

        $targetUser = null;

        if (! empty($validated['user_id'])) {
            $targetUser = User::query()
                ->whereKey($validated['user_id'])
                ->whereHas('schools', fn ($query) => $query->where('schools.id', $school->id))
                ->firstOrFail();

            if (! $targetUser->hasRole($validated['role'])) {
                return back()
                    ->withErrors('User yang dipilih tidak memiliki jenis akun tersebut.')
                    ->withInput();
            }
        }

        $request->session()->put(EffectiveAccess::SESSION_KEY, [
            'active' => true,
            'role' => $validated['role'],
            'role_label' => self::ROLES[$validated['role']],
            'school_id' => $school->id,
            'school_name' => $school->name,
            'user_id' => $targetUser?->id,
            'user_name' => $targetUser?->name,
        ]);

        return redirect()->route('dashboard')->with('success', 'Mode lihat sebagai berhasil diaktifkan.');
    }

    public function destroy(Request $request): RedirectResponse
    {
        $request->session()->forget(EffectiveAccess::SESSION_KEY);

        return redirect()->route('view-as.index')->with('success', 'Mode lihat sebagai sudah dinonaktifkan.');
    }
}
