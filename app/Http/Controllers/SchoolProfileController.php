<?php

namespace App\Http\Controllers;

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

            $extension = strtolower($logo->getClientOriginalExtension() ?: 'png');
            $fileName = 'school-'.$school->id.'-'.now()->format('YmdHis').'.'.$extension;
            $targetDirectory = public_path('uploads/school-logos');

            if (! is_dir($targetDirectory) && ! mkdir($targetDirectory, 0755, true)) {
                return back()
                    ->withErrors('Folder upload logo sekolah gagal dibuat.')
                    ->withInput();
            }

            $logo->move($targetDirectory, $fileName);

            $logoPath = 'uploads/school-logos/'.$fileName;

            if ($school->logo_path && is_file(public_path($school->logo_path))) {
                unlink(public_path($school->logo_path));
            }
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
        return $request->user()
            ->schools()
            ->wherePivot('status', 'active')
            ->where('schools.status', 'active')
            ->first();
    }
}
