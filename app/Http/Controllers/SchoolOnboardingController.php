<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\SchoolClass;
use App\Models\Semester;
use App\Models\Subject;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class SchoolOnboardingController extends Controller
{
    public function edit(Request $request): View|RedirectResponse
    {
        $school = $this->activeSchool($request);

        if (! $school) {
            return redirect()->route('dashboard')->withErrors('Akun Anda belum terhubung ke sekolah aktif.');
        }

        if ($school->onboarding_completed_at) {
            return redirect()->route('dashboard');
        }

        return view('school-onboarding.edit', compact('school'));
    }

    public function update(Request $request): RedirectResponse
    {
        $school = $this->activeSchool($request);

        if (! $school) {
            return redirect()->route('dashboard')->withErrors('Akun Anda belum terhubung ke sekolah aktif.');
        }

        if ($school->onboarding_completed_at) {
            return redirect()->route('dashboard');
        }

        $validated = $request->validate([
            'school_name' => ['required', 'string', 'max:255'],
            'npsn' => ['nullable', 'string', 'max:50', 'unique:schools,npsn,'.$school->id],
            'level' => ['required', 'string', 'max:50'],
            'address' => ['nullable', 'string', 'max:1000'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
            'academic_year_name' => ['required', 'string', 'max:50'],
            'academic_year_starts_at' => ['required', 'date'],
            'academic_year_ends_at' => ['required', 'date', 'after:academic_year_starts_at'],
            'semester_name' => ['required', 'string', 'max:50'],
            'semester_starts_at' => ['required', 'date'],
            'semester_ends_at' => ['required', 'date', 'after:semester_starts_at'],
            'classes' => ['required', 'string'],
            'subjects' => ['required', 'string'],
        ]);

        $classRows = $this->parseLines($validated['classes']);
        $subjectRows = $this->parseLines($validated['subjects']);

        if (empty($classRows) || empty($subjectRows)) {
            return back()
                ->withErrors('Minimal isi 1 kelas dan 1 mata pelajaran.')
                ->withInput();
        }

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

        DB::transaction(function () use ($school, $validated, $classRows, $subjectRows, $logoPath) {
            $school->update([
                'name' => $validated['school_name'],
                'npsn' => $validated['npsn'] ?? null,
                'level' => $validated['level'],
                'address' => $validated['address'] ?? null,
                'phone' => $validated['phone'] ?? null,
                'email' => $validated['email'] ?? null,
                'logo_path' => $logoPath,
            ]);

            $school->academicYears()->update(['is_active' => false]);

            $academicYear = AcademicYear::create([
                'school_id' => $school->id,
                'name' => $validated['academic_year_name'],
                'starts_at' => $validated['academic_year_starts_at'],
                'ends_at' => $validated['academic_year_ends_at'],
                'is_active' => true,
            ]);

            $school->semesters()->update(['is_active' => false]);

            Semester::create([
                'school_id' => $school->id,
                'academic_year_id' => $academicYear->id,
                'name' => $validated['semester_name'],
                'starts_at' => $validated['semester_starts_at'],
                'ends_at' => $validated['semester_ends_at'],
                'is_active' => true,
            ]);

            foreach ($classRows as $classRow) {
                SchoolClass::create([
                    'school_id' => $school->id,
                    'academic_year_id' => $academicYear->id,
                    'name' => $classRow['name'],
                    'level' => $classRow['level'],
                    'is_active' => true,
                ]);
            }

            foreach ($subjectRows as $subjectRow) {
                Subject::create([
                    'school_id' => $school->id,
                    'name' => $subjectRow['name'],
                    'code' => $subjectRow['code'],
                    'is_active' => true,
                ]);
            }

            $school->update([
                'onboarding_completed_at' => now(),
            ]);
        });

        return redirect()
            ->route('dashboard')
            ->with('success', 'Onboarding sekolah berhasil diselesaikan.');
    }

    private function activeSchool(Request $request)
    {
        return $request->user()
            ->schools()
            ->wherePivot('status', 'active')
            ->where('schools.status', 'active')
            ->first();
    }

    private function parseLines(string $value): array
    {
        return collect(preg_split('/\r\n|\r|\n/', $value))
            ->map(fn ($line) => trim($line))
            ->filter()
            ->map(function ($line) {
                [$name, $meta] = array_pad(array_map('trim', explode('|', $line, 2)), 2, null);

                return [
                    'name' => $name,
                    'level' => $meta,
                    'code' => $meta,
                ];
            })
            ->values()
            ->all();
    }
}
