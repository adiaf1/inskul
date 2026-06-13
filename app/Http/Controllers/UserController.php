<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\School;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use App\Models\Role;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Ambil parameter pencarian (jika ada)
        $search = $request->input('search');
        $schoolId = $request->input('school_id');
        $roleName = $request->input('role');
        $schools = School::where('status', 'active')
            ->orderBy('name')
            ->get(['id', 'name']);
        $roles = Role::orderBy('name')->get(['id', 'name']);

        // Query pengguna, filter kalau ada pencarian
        $users = User::with(['roles', 'schools'])
            ->when($search, function ($query, $search) {
                return $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->when($schoolId, function ($query, $schoolId) {
                return $query->whereHas('schools', function ($schoolQuery) use ($schoolId) {
                    $schoolQuery->where('schools.id', $schoolId)
                        ->where('schools.status', 'active')
                        ->where('school_user.status', 'active');
                });
            })
            ->when($roleName, function ($query, $roleName) {
                return $query->whereHas('roles', function ($roleQuery) use ($roleName) {
                    $roleQuery->where('name', $roleName);
                });
            })
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString(); // pagination

        return view('users.index', compact('users', 'schools', 'schoolId', 'roles', 'roleName'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6',
            'role' => 'required|exists:roles,name',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => bcrypt($validated['password']),
        ]);

        $role = Role::findByName($validated['role']);
        $user->assignRole($role);

        return redirect()->route('users.index')->with('success', 'User baru Berhasil Dibuat!');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        // Validasi input
        $validator = \Validator::make($request->all(), [
            'name' => 'required|string|max:255', // Validasi nama pengguna
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id, // Validasi email
            'roles' => 'required|array',
            'roles.*' => 'exists:roles,name', // Memastikan setiap role ada dalam tabel roles
        ]);
        

        // Cek jika validasi gagal
        if ($validator->fails()) {
            return redirect()->route('users.edit', $user->id) // Kembali ke halaman edit pengguna
                            ->withErrors($validator) // Menyimpan kesalahan dalam session
                            ->withInput(); // Menyimpan input agar tetap terlihat
        }

        // Jika validasi berhasil, perbarui informasi pengguna
        $user->name = $request->name; // Perbarui nama
        $user->email = $request->email; // Perbarui email
        $user->save(); // Simpan perubahan pada pengguna

        // Sinkronkan roles
        $user->syncRoles($request->roles);

        // Kembali ke halaman index dengan notifikasi sukses
        return redirect()->route('users.index')->with('success', 'User Berhasil Diperbaharui.'); // Kembali ke index dengan sukses
    }

    /**
     * Reset the specified user's password.
     */
    public function resetPassword(Request $request, User $user)
    {
        $validated = $request->validate([
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user->update([
            'password' => Hash::make($validated['password']),
        ]);

        return redirect()->route('users.index')->with('success', 'Kata sandi pengguna berhasil direset.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        $user->delete();

        return redirect()->route('users.index')->with('success', 'User Berhasil Dihapus!.');
    }

    /**
     * Search the specified resource from storage.
     */

    public function search(Request $request)
    {
        return $this->index($request);
    }
}
