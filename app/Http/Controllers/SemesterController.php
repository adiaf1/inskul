<?php

namespace App\Http\Controllers;

use App\Models\Semester;
use App\Support\EffectiveAccess;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SemesterController extends Controller
{
    public function index(Request $request): View|RedirectResponse
    {
        $school = $this->activeSchool($request);

        if (! $school) {
            return redirect()->route('dashboard')->withErrors('Akun Anda belum terhubung ke sekolah aktif.');
        }

        $search = $request->input('search');
        $academicYearId = $request->input('academic_year_id');
        $academicYears = $school->academicYears()->orderByDesc('is_active')->orderBy('name')->get();

        $semesters = $school->semesters()
            ->with('academicYear')
            ->when($search, fn ($query) => $query->where('name', 'like', "%{$search}%"))
            ->when($academicYearId, fn ($query) => $query->where('academic_year_id', $academicYearId))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('semesters.index', compact('school', 'semesters', 'academicYears', 'academicYearId'));
    }

    public function store(Request $request): RedirectResponse
    {
        $school = $this->activeSchool($request);

        if (! $school) {
            return redirect()->route('dashboard')->withErrors('Akun Anda belum terhubung ke sekolah aktif.');
        }

        $validated = $request->validate($this->rules($school->id));

        DB::transaction(function () use ($school, $validated, $request) {
            if ($request->boolean('is_active')) {
                $school->semesters()->update(['is_active' => false]);
            }

            Semester::create([
                'school_id' => $school->id,
                'academic_year_id' => $validated['academic_year_id'],
                'name' => $validated['name'],
                'starts_at' => $validated['starts_at'],
                'ends_at' => $validated['ends_at'],
                'is_active' => $request->boolean('is_active'),
            ]);
        });

        return redirect()->route('semesters.index')->with('success', 'Semester berhasil dibuat.');
    }

    public function update(Request $request, Semester $semester): RedirectResponse
    {
        $school = $this->activeSchool($request);

        if (! $school || $semester->school_id !== $school->id) {
            abort(403);
        }

        $validated = $request->validate($this->rules($school->id));

        DB::transaction(function () use ($school, $semester, $validated, $request) {
            if ($request->boolean('is_active')) {
                $school->semesters()
                    ->whereKeyNot($semester->id)
                    ->update(['is_active' => false]);
            }

            $semester->update([
                'academic_year_id' => $validated['academic_year_id'],
                'name' => $validated['name'],
                'starts_at' => $validated['starts_at'],
                'ends_at' => $validated['ends_at'],
                'is_active' => $request->boolean('is_active'),
            ]);
        });

        return redirect()->route('semesters.index')->with('success', 'Semester berhasil diperbarui.');
    }

    public function destroy(Request $request, Semester $semester): RedirectResponse
    {
        $school = $this->activeSchool($request);

        if (! $school || $semester->school_id !== $school->id) {
            abort(403);
        }

        $semester->delete();

        return redirect()->route('semesters.index')->with('success', 'Semester berhasil dihapus.');
    }

    private function rules(int $schoolId): array
    {
        return [
            'academic_year_id' => [
                'required',
                Rule::exists('academic_years', 'id')->where('school_id', $schoolId),
            ],
            'name' => ['required', 'string', 'max:50'],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['required', 'date', 'after:starts_at'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    private function activeSchool(Request $request)
    {
        return EffectiveAccess::school($request);
    }
}
