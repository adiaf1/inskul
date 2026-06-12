<?php

namespace App\Http\Controllers;

use App\Models\Classroom;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ClassroomController extends Controller
{
    public function index(Request $request): View|RedirectResponse
    {
        $school = $this->activeSchool($request);

        if (! $school) {
            return redirect()->route('dashboard')->withErrors('Akun Anda belum terhubung ke sekolah aktif.');
        }

        $search = $request->input('search');
        $academicYearId = $request->input('academic_year_id');
        $semesterId = $request->input('semester_id');
        $status = $request->input('status');

        $academicYears = $school->academicYears()->orderByDesc('is_active')->orderBy('name')->get();
        $semesters = $school->semesters()->with('academicYear')->orderByDesc('is_active')->orderBy('name')->get();

        $classrooms = $school->classrooms()
            ->with(['academicYear', 'semester', 'schoolClass', 'homeroomTeacher.user', 'students'])
            ->when($search, function ($query, $search) {
                $query->where(function ($inner) use ($search) {
                    $inner->where('name', 'like', "%{$search}%")
                        ->orWhereHas('schoolClass', fn ($classQuery) => $classQuery->where('name', 'like', "%{$search}%"))
                        ->orWhereHas('homeroomTeacher.user', fn ($userQuery) => $userQuery->where('name', 'like', "%{$search}%"));
                });
            })
            ->when($academicYearId, fn ($query) => $query->where('academic_year_id', $academicYearId))
            ->when($semesterId, fn ($query) => $query->where('semester_id', $semesterId))
            ->when($status !== null && $status !== '', fn ($query) => $query->where('is_active', $status === 'active'))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('classrooms.index', compact(
            'school',
            'classrooms',
            'academicYears',
            'semesters',
            'academicYearId',
            'semesterId',
            'status'
        ));
    }

    public function create(Request $request): View|RedirectResponse
    {
        $school = $this->activeSchool($request);

        if (! $school) {
            return redirect()->route('dashboard')->withErrors('Akun Anda belum terhubung ke sekolah aktif.');
        }

        return view('classrooms.create', [
            'school' => $school,
            'classroom' => null,
            'selectedStudents' => old('student_ids', []),
            ...$this->formOptions($school),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $school = $this->activeSchool($request);

        if (! $school) {
            return redirect()->route('dashboard')->withErrors('Akun Anda belum terhubung ke sekolah aktif.');
        }

        $validated = $request->validate($this->rules($school->id));

        DB::transaction(function () use ($school, $validated, $request) {
            $classroom = Classroom::create([
                'school_id' => $school->id,
                'academic_year_id' => $validated['academic_year_id'],
                'semester_id' => $validated['semester_id'],
                'school_class_id' => $validated['school_class_id'],
                'homeroom_teacher_id' => $validated['homeroom_teacher_id'] ?? null,
                'name' => $validated['name'],
                'capacity' => $validated['capacity'] ?? null,
                'is_active' => $request->boolean('is_active'),
            ]);

            $classroom->students()->sync($this->studentSyncPayload($validated['student_ids'] ?? []));
        });

        return redirect()->route('classrooms.index')->with('success', 'Rombel berhasil dibuat.');
    }

    public function update(Request $request, Classroom $classroom): RedirectResponse
    {
        $school = $this->activeSchool($request);

        if (! $school || $classroom->school_id !== $school->id) {
            abort(403);
        }

        $validated = $request->validate($this->rules($school->id, $classroom->id));

        DB::transaction(function () use ($classroom, $validated, $request) {
            $classroom->update([
                'academic_year_id' => $validated['academic_year_id'],
                'semester_id' => $validated['semester_id'],
                'school_class_id' => $validated['school_class_id'],
                'homeroom_teacher_id' => $validated['homeroom_teacher_id'] ?? null,
                'name' => $validated['name'],
                'capacity' => $validated['capacity'] ?? null,
                'is_active' => $request->boolean('is_active'),
            ]);

            $classroom->students()->sync($this->studentSyncPayload($validated['student_ids'] ?? []));
        });

        return redirect()->route('classrooms.index')->with('success', 'Rombel berhasil diperbarui.');
    }

    public function edit(Request $request, Classroom $classroom): View|RedirectResponse
    {
        $school = $this->activeSchool($request);

        if (! $school || $classroom->school_id !== $school->id) {
            abort(403);
        }

        $classroom->load('students');

        return view('classrooms.edit', [
            'school' => $school,
            'classroom' => $classroom,
            'selectedStudents' => old('student_ids', $classroom->students->pluck('id')->map(fn ($id) => (string) $id)->all()),
            ...$this->formOptions($school),
        ]);
    }

    public function destroy(Request $request, Classroom $classroom): RedirectResponse
    {
        $school = $this->activeSchool($request);

        if (! $school || $classroom->school_id !== $school->id) {
            abort(403);
        }

        $classroom->delete();

        return redirect()->route('classrooms.index')->with('success', 'Rombel berhasil dihapus.');
    }

    private function rules(int $schoolId, ?int $classroomId = null): array
    {
        return [
            'academic_year_id' => ['required', Rule::exists('academic_years', 'id')->where('school_id', $schoolId)],
            'semester_id' => ['required', Rule::exists('semesters', 'id')->where('school_id', $schoolId)],
            'school_class_id' => ['required', Rule::exists('school_classes', 'id')->where('school_id', $schoolId)],
            'homeroom_teacher_id' => ['nullable', Rule::exists('teachers', 'id')->where('school_id', $schoolId)],
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('classrooms', 'name')
                    ->where('school_id', $schoolId)
                    ->where('academic_year_id', request('academic_year_id'))
                    ->where('semester_id', request('semester_id'))
                    ->ignore($classroomId),
            ],
            'capacity' => ['nullable', 'integer', 'min:1', 'max:999'],
            'student_ids' => ['nullable', 'array'],
            'student_ids.*' => [Rule::exists('students', 'id')->where('school_id', $schoolId)],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    private function studentSyncPayload(array $studentIds): array
    {
        return collect($studentIds)
            ->filter()
            ->unique()
            ->mapWithKeys(fn ($studentId) => [$studentId => ['status' => 'active']])
            ->all();
    }

    private function formOptions($school): array
    {
        $students = $school->students()->with('user')->where('is_active', true)->get()->sortBy('user.name');

        return [
            'academicYears' => $school->academicYears()->orderByDesc('is_active')->orderBy('name')->get(),
            'semesters' => $school->semesters()->with('academicYear')->orderByDesc('is_active')->orderBy('name')->get(),
            'schoolClasses' => $school->classes()->where('is_active', true)->orderBy('level')->orderBy('name')->get(),
            'teachers' => $school->teachers()->with('user')->where('is_active', true)->get()->sortBy('user.name'),
            'students' => $students,
            'studentCohorts' => $students->pluck('entry_year')->filter()->unique()->sortDesc()->values(),
        ];
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
