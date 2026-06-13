<?php

namespace App\Http\Controllers;

use App\Support\SchoolFileStorage;
use App\Support\EffectiveAccess;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SchoolProfileController extends Controller
{
    public function edit(Request $request): View|RedirectResponse
    {
        $school = $this->activeSchool($request);

        if (! $school) {
            return redirect()->route('dashboard')->withErrors('Akun Anda belum terhubung ke sekolah aktif.');
        }

        return view('school-profile.edit', compact('school'));
    }

    public function update(Request $request): RedirectResponse
    {
        $school = $this->activeSchool($request);

        if (! $school) {
            return redirect()->route('dashboard')->withErrors('Akun Anda belum terhubung ke sekolah aktif.');
        }

        $validated = $request->validate([
            'school_name' => ['required', 'string', 'max:255'],
            'npsn' => ['nullable', 'string', 'max:50', 'unique:schools,npsn,'.$school->id],
            'level' => ['required', 'string', 'max:50'],
            'address' => ['nullable', 'string', 'max:1000'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
        ]);

        $logoPath = $school->logo_path;

        if ($request->hasFile('logo')) {
            $logo = $request->file('logo');

            if (! $logo->isValid() || empty($logo->getPathname()) || ! is_file($logo->getPathname())) {
                return back()
                    ->withErrors('Logo sekolah gagal diunggah. Silakan pilih ulang file logo.')
                    ->withInput();
            }

            $folderSchool = clone $school;
            $folderSchool->name = $validated['school_name'];
            $folderSchool->npsn = $validated['npsn'] ?? null;

            $logoPath = SchoolFileStorage::store($logo, $folderSchool, 'logos', 'logo-sekolah');
            SchoolFileStorage::delete($school->logo_path);
        }

        $school->update([
            'name' => $validated['school_name'],
            'npsn' => $validated['npsn'] ?? null,
            'level' => $validated['level'],
            'address' => $validated['address'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'email' => $validated['email'] ?? null,
            'logo_path' => $logoPath,
        ]);

        return redirect()
            ->route('school-profile.edit')
            ->with('success', 'Profil sekolah berhasil diperbarui.');
    }

    private function activeSchool(Request $request)
    {
        return EffectiveAccess::school($request);
    }
}
