<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\School;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;
use App\Models\Role;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'school_name' => ['required', 'string', 'max:255'],
            'npsn' => ['nullable', 'string', 'max:50', 'unique:schools,npsn'],
            'level' => ['required', 'string', 'max:50'],
            'address' => ['nullable', 'string', 'max:1000'],
            'school_phone' => ['nullable', 'string', 'max:50'],
            'school_email' => ['nullable', 'string', 'lowercase', 'email', 'max:255'],
            'admin_name' => ['required', 'string', 'max:255'],
            'admin_email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class.',email'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        DB::transaction(function () use ($request) {
            $schoolAdminRole = Role::findByName('school_admin');

            $school = School::create([
                'name' => $request->school_name,
                'npsn' => $request->npsn,
                'level' => $request->level,
                'address' => $request->address,
                'phone' => $request->school_phone,
                'email' => $request->school_email,
                'status' => 'pending',
            ]);

            $user = User::create([
                'name' => $request->admin_name,
                'email' => $request->admin_email,
                'password' => Hash::make($request->password),
                'status' => 'pending',
            ]);

            $user->assignRole($schoolAdminRole);

            $school->users()->attach($user->id, [
                'role_id' => $schoolAdminRole->id,
                'status' => 'pending',
            ]);

            event(new Registered($user));
        });

        return redirect()
            ->route('register.success')
            ->with('success', 'Registrasi sekolah berhasil dikirim. Akun admin sekolah akan aktif setelah disetujui Super Admin.');
    }
}
