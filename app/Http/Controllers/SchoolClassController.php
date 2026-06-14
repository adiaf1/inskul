<?php

namespace App\Http\Controllers;

use App\Models\SchoolClass;
use App\Support\EffectiveAccess;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SchoolClassController extends Controller
{
    public function index(Request $request): View|RedirectResponse
    {
        $school = $this->activeSchool($request);

        if (! $school) {
            return redirect()->route('dashboard')->withErrors('Akun Anda belum terhubung ke sekolah aktif.');
        }

        $search = $request->input('search');
        $academicYearId = $request->input('academic_year_id');
        $status = $request->input('status');
        $academicYears = $school->academicYears()->orderByDesc('is_active')->orderBy('name')->get();

        $schoolClasses = $school->classes()
            ->with('academicYear')
            ->when($search, function ($query, $search) {
                $query->where(function ($inner) use ($search) {
                    $inner->where('name', 'like', "%{$search}%")
                        ->orWhere('level', 'like', "%{$search}%");
                });
            })
            ->when($academicYearId, fn ($query) => $query->where('academic_year_id', $academicYearId))
            ->when($status !== null && $status !== '', fn ($query) => $query->where('is_active', $status === 'active'))
            ->orderBy('level')
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view('school-classes.index', compact('school', 'schoolClasses', 'academicYears', 'academicYearId', 'status'));
    }

    public function store(Request $request): RedirectResponse
    {
        $school = $this->activeSchool($request);

        if (! $school) {
            return redirect()->route('dashboard')->withErrors('Akun Anda belum terhubung ke sekolah aktif.');
        }

        $validated = $request->validate($this->rules($school->id));

        SchoolClass::create([
            'school_id' => $school->id,
            'academic_year_id' => $validated['academic_year_id'],
            'name' => $validated['name'],
            'level' => $validated['level'] ?? null,
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('school-classes.index')->with('success', 'Kelas berhasil dibuat.');
    }

    public function update(Request $request, SchoolClass $schoolClass): RedirectResponse
    {
        $school = $this->activeSchool($request);

        if (! $school || $schoolClass->school_id !== $school->id) {
            abort(403);
        }

        $validated = $request->validate($this->rules($school->id));

        $schoolClass->update([
            'academic_year_id' => $validated['academic_year_id'],
            'name' => $validated['name'],
            'level' => $validated['level'] ?? null,
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('school-classes.index')->with('success', 'Kelas berhasil diperbarui.');
    }

    public function destroy(Request $request, SchoolClass $schoolClass): RedirectResponse
    {
        $school = $this->activeSchool($request);

        if (! $school || $schoolClass->school_id !== $school->id) {
            abort(403);
        }

        $schoolClass->delete();

        return redirect()->route('school-classes.index')->with('success', 'Kelas berhasil dihapus.');
    }

    private function rules(string $schoolId): array
    {
        return [
            'academic_year_id' => [
                'required',
                Rule::exists('academic_years', 'id')->where('school_id', $schoolId),
            ],
            'name' => ['required', 'string', 'max:100'],
            'level' => ['nullable', 'string', 'max:50'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    private function activeSchool(Request $request)
    {
        return EffectiveAccess::school($request);
    }
}
