<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Support\EffectiveAccess;
use App\Support\SchoolFileStorage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    /**
     * Delete the authenticated user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }

    /**
     * Display the user's profile form.
     */
    public function index(): View
    {

        $user = Auth::user(); // Ambil pengguna yang sedang login
        $menus = Menu::getMenuForUser($user); // Ambil menu untuk pengguna
        
        return view('dashboard', compact('menus')); // Tampilkan view dengan data menu
        
    }

    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        $user = $request->user();
        return view('profile.edit', compact('user'));
    }

    /**
     * Update the user's profile information.
     */

    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();
        if ($request->filled('new_password')) {
            $request->validate([
                'old_password' => 'required',
                'new_password' => 'required|string|min:6|same:confirm_password',
            ]);
        
            if (!Hash::check($request->old_password, $user->password)) {
                return back()->withErrors(['old_password' => 'Password lama tidak sesuai'])->withInput();
            }
        
            $user->password = Hash::make($request->new_password);
        }
        

        // Cek dan simpan foto profil
        if ($request->hasFile('profile_picture')) {
            $request->validate([
                'profile_picture' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
            ]);

            $profilePicture = $request->file('profile_picture');

            if (! $profilePicture->isValid() || empty($profilePicture->getPathname()) || ! is_file($profilePicture->getPathname())) {
                return back()->withErrors('Foto profil gagal diunggah. Silakan pilih ulang file foto.')->withInput();
            }

            if ($user->hasRole('super_admin')) {
                if ($user->profile_picture && $user->profile_picture !== 'default.png' && File::exists(public_path('assets/img/avatars/' . $user->profile_picture))) {
                    File::delete(public_path('assets/img/avatars/' . $user->profile_picture));
                }

                $filename = time() . '.' . $profilePicture->getClientOriginalExtension();
                $profilePicture->move(public_path('assets/img/avatars'), $filename);
                $user->profile_picture = $filename;
            } else {
                $school = $this->activeSchool($request);

                if (! $school) {
                    return back()->withErrors('Akun Anda belum terhubung ke sekolah aktif.')->withInput();
                }

                if ($user->profile_picture && $user->profile_picture !== 'default.png') {
                    SchoolFileStorage::delete($user->profile_picture);
                }

                $user->profile_picture = SchoolFileStorage::store($profilePicture, $school, 'profiles', 'foto-profil');
            }
        }

        // Ubah data umum
        $user->fill($request->validated());

        // Reset email verifikasi jika email berubah
        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        // Cek apakah ada input password
        if ($request->filled('password')) {
            $request->validate([
                'password' => 'required|string|min:6|confirmed'
            ]);

            $user->password = Hash::make($request->password);
        }

        $user->save();

        return Redirect::route('profile.edit')->with('success', 'Profile updated successfully!');
    }

    private function activeSchool(Request $request)
    {
        return EffectiveAccess::school($request);
    }

}
