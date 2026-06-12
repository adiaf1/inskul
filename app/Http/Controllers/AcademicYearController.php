<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AcademicYearController extends Controller
{
    public function index(Request $request): View|RedirectResponse
    {
        $school = $this->activeSchool($request);

        if (! $school) {
            return redirect()->route('dashboard')->withErrors('Akun Anda belum terhubung ke sekolah aktif.');
        }

        $search = $request->input('search');

        $academicYears = $school->academicYears()
            ->when($search, fn ($query) => $query->where('name', 'like', "%{$search}%"))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('academic-years.index', compact('school', 'academicYears'));
    }

    public function store(Request $request): RedirectResponse
    {
        $school = $this->activeSchool($request);

        if (! $school) {
            return redirect()->route('dashboard')->withErrors('Akun Anda belum terhubung ke sekolah aktif.');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:50'],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['required', 'date', 'after:starts_at'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        DB::transaction(function () use ($school, $validated, $request) {
            if ($request->boolean('is_active')) {
                $school->academicYears()->update(['is_active' => false]);
            }

            AcademicYear::create([
                'school_id' => $school->id,
                'name' => $validated['name'],
                'starts_at' => $validated['starts_at'],
                'ends_at' => $validated['ends_at'],
                'is_active' => $request->boolean('is_active'),
            ]);
        });

        return redirect()->route('academic-years.index')->with('success', 'Tahun ajaran berhasil dibuat.');
    }

    public function update(Request $request, AcademicYear $academicYear): RedirectResponse
    {
        $school = $this->activeSchool($request);

        if (! $school || $academicYear->school_id !== $school->id) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:50'],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['required', 'date', 'after:starts_at'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        DB::transaction(function () use ($school, $academicYear, $validated, $request) {
            if ($request->boolean('is_active')) {
                $school->academicYears()
                    ->whereKeyNot($academicYear->id)
                    ->update(['is_active' => false]);
            }

            $academicYear->update([
                'name' => $validated['name'],
                'starts_at' => $validated['starts_at'],
                'ends_at' => $validated['ends_at'],
                'is_active' => $request->boolean('is_active'),
            ]);
        });

        return redirect()->route('academic-years.index')->with('success', 'Tahun ajaran berhasil diperbarui.');
    }

    public function destroy(Request $request, AcademicYear $academicYear): RedirectResponse
    {
        $school = $this->activeSchool($request);

        if (! $school || $academicYear->school_id !== $school->id) {
            abort(403);
        }

        if ($academicYear->semesters()->exists() || $school->classes()->where('academic_year_id', $academicYear->id)->exists()) {
            return back()->withErrors('Tahun ajaran tidak bisa dihapus karena sudah dipakai oleh semester atau kelas.');
        }

        $academicYear->delete();

        return redirect()->route('academic-years.index')->with('success', 'Tahun ajaran berhasil dihapus.');
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
