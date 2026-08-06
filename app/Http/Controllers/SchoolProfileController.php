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
            'daily_check_in_time' => ['required', 'date_format:H:i'],
            'daily_late_tolerance_minutes' => ['required', 'integer', 'min:0', 'max:240'],
            'daily_check_out_time' => ['required', 'date_format:H:i'],
            'daily_early_leave_tolerance_minutes' => ['required', 'integer', 'min:0', 'max:240'],
            'daily_min_checkout_minutes' => ['required', 'integer', 'min:0', 'max:720'],
            'school_attendance_days' => ['required', 'array', 'min:1'],
            'school_attendance_days.*' => ['required', 'integer', 'between:1,7'],
            'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
            'nametag_background' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'remove_nametag_background' => ['nullable', 'boolean'],
        ]);

        $schoolAttendanceDays = collect($validated['school_attendance_days'])
            ->map(fn ($day) => (int) $day)
            ->unique()
            ->sort()
            ->values()
            ->all();

        $logoPath = $school->logo_path;
        $nametagBackgroundPath = $school->nametag_background_path;
        $folderSchool = clone $school;
        $folderSchool->name = $validated['school_name'];
        $folderSchool->npsn = $validated['npsn'] ?? null;

        if ($request->hasFile('logo')) {
            $logo = $request->file('logo');

            if (! $logo->isValid() || empty($logo->getPathname()) || ! is_file($logo->getPathname())) {
                return back()
                    ->withErrors('Logo sekolah gagal diunggah. Silakan pilih ulang file logo.')
                    ->withInput();
            }

            $logoPath = SchoolFileStorage::store($logo, $folderSchool, 'logos', 'logo-sekolah');
            SchoolFileStorage::delete($school->logo_path);
        }

        if ($request->hasFile('nametag_background')) {
            $background = $request->file('nametag_background');

            if (! $background->isValid() || empty($background->getPathname()) || ! is_file($background->getPathname())) {
                return back()
                    ->withErrors('Background nametag gagal diunggah. Silakan pilih ulang file background.')
                    ->withInput();
            }

            $nametagBackgroundPath = SchoolFileStorage::store($background, $folderSchool, 'nametags', 'background-nametag');
            SchoolFileStorage::delete($school->nametag_background_path);
        } elseif ($request->boolean('remove_nametag_background')) {
            SchoolFileStorage::delete($school->nametag_background_path);
            $nametagBackgroundPath = null;
        }

        $school->update([
            'name' => $validated['school_name'],
            'npsn' => $validated['npsn'] ?? null,
            'level' => $validated['level'],
            'address' => $validated['address'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'email' => $validated['email'] ?? null,
            'daily_check_in_time' => $validated['daily_check_in_time'],
            'daily_late_tolerance_minutes' => $validated['daily_late_tolerance_minutes'],
            'daily_check_out_time' => $validated['daily_check_out_time'],
            'daily_early_leave_tolerance_minutes' => $validated['daily_early_leave_tolerance_minutes'],
            'daily_min_checkout_minutes' => $validated['daily_min_checkout_minutes'],
            'school_attendance_days' => $schoolAttendanceDays,
            'logo_path' => $logoPath,
            'nametag_background_path' => $nametagBackgroundPath,
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
