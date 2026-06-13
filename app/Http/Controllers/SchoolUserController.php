<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Support\EffectiveAccess;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;
use App\Models\Role;

class SchoolUserController extends Controller
{
    private const SCHOOL_ROLES = [
        'principal',
        'teacher',
        'student',
        'parent',
    ];

    public function index(Request $request): View|RedirectResponse
    {
        $school = $this->activeSchool($request);

        if (! $school) {
            return redirect()
                ->route('dashboard')
                ->withErrors('Akun admin sekolah belum terhubung ke sekolah aktif.');
        }

        $search = $request->input('search');
        $roles = Role::whereIn('name', self::SCHOOL_ROLES)->orderBy('name')->get();

        $users = $school->users()
            ->with('roles')
            ->whereHas('roles', fn ($query) => $query->whereIn('name', self::SCHOOL_ROLES))
            ->when($search, function ($query, $search) {
                $query->where(function ($inner) use ($search) {
                    $inner->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view('school-users.index', compact('school', 'users', 'roles'));
    }

    public function store(Request $request): RedirectResponse
    {
        $school = $this->activeSchool($request);

        if (! $school) {
            return back()->withErrors('Akun admin sekolah belum terhubung ke sekolah aktif.');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'role' => ['required', 'string', 'in:'.implode(',', self::SCHOOL_ROLES)],
        ]);

        $role = Role::findByName($validated['role']);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'status' => 'active',
        ]);

        $user->assignRole($role);

        $school->users()->attach($user->id, [
            'role_id' => $role->id,
            'status' => 'active',
        ]);

        return redirect()
            ->route('school-users.index')
            ->with('success', 'Akun pengguna sekolah berhasil dibuat.');
    }

    private function activeSchool(Request $request)
    {
        return EffectiveAccess::school($request);
    }
}
