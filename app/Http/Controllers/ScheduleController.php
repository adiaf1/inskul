<?php

namespace App\Http\Controllers;

use App\Models\Schedule;
use App\Support\EffectiveAccess;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ScheduleController extends Controller
{
    private const DAYS = [
        1 => 'Senin',
        2 => 'Selasa',
        3 => 'Rabu',
        4 => 'Kamis',
        5 => 'Jumat',
        6 => 'Sabtu',
        7 => 'Minggu',
    ];

    public function index(Request $request): View|RedirectResponse
    {
        $school = $this->activeSchool($request);

        if (! $school) {
            return redirect()->route('dashboard')->withErrors('Akun Anda belum terhubung ke sekolah aktif.');
        }

        $academicYearId = $request->input('academic_year_id');
        $semesterId = $request->input('semester_id');
        $classroomId = $request->input('classroom_id');
        $roomId = $request->input('room_id');
        $dayOfWeek = $request->input('day_of_week');
        $status = $request->input('status');

        $academicYears = $school->academicYears()->orderByDesc('is_active')->orderBy('name')->get();
        $semesters = $school->semesters()->with('academicYear')->orderByDesc('is_active')->orderBy('name')->get();
        $classrooms = $school->classrooms()->with(['academicYear', 'semester'])->where('is_active', true)->orderBy('name')->get();
        $subjects = $school->subjects()->where('is_active', true)->orderBy('name')->get();
        $teachers = $school->teachers()->with('user')->where('is_active', true)->get()->sortBy('user.name');
        $rooms = $school->rooms()->where('is_active', true)->orderBy('name')->get();
        $days = self::DAYS;

        $schedules = $school->schedules()
            ->with(['academicYear', 'semester', 'classroom', 'subject', 'teacher.user', 'physicalRoom'])
            ->when($academicYearId, fn ($query) => $query->where('academic_year_id', $academicYearId))
            ->when($semesterId, fn ($query) => $query->where('semester_id', $semesterId))
            ->when($classroomId, fn ($query) => $query->where('classroom_id', $classroomId))
            ->when($roomId, fn ($query) => $query->where('room_id', $roomId))
            ->when($dayOfWeek, fn ($query) => $query->where('day_of_week', $dayOfWeek))
            ->when($status !== null && $status !== '', fn ($query) => $query->where('is_active', $status === 'active'))
            ->orderBy('day_of_week')
            ->orderBy('starts_at')
            ->paginate(10)
            ->withQueryString();

        return view('schedules.index', compact(
            'school',
            'schedules',
            'academicYears',
            'semesters',
            'classrooms',
            'subjects',
            'teachers',
            'rooms',
            'days',
            'academicYearId',
            'semesterId',
            'classroomId',
            'roomId',
            'dayOfWeek',
            'status'
        ));
    }

    public function store(Request $request): RedirectResponse
    {
        $school = $this->activeSchool($request);

        if (! $school) {
            return redirect()->route('dashboard')->withErrors('Akun Anda belum terhubung ke sekolah aktif.');
        }

        $validated = $request->validate($this->rules($school->id));
        $this->ensureNoConflicts($school->id, $validated);

        Schedule::create([
            'school_id' => $school->id,
            ...$validated,
            'teacher_id' => $validated['teacher_id'] ?? null,
            'room_id' => $validated['room_id'] ?? null,
            'room' => $validated['room'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('schedules.index')->with('success', 'Jadwal berhasil dibuat.');
    }

    public function update(Request $request, Schedule $schedule): RedirectResponse
    {
        $school = $this->activeSchool($request);

        if (! $school || $schedule->school_id !== $school->id) {
            abort(403);
        }

        $validated = $request->validate($this->rules($school->id));
        $this->ensureNoConflicts($school->id, $validated, $schedule->id);

        $schedule->update([
            ...$validated,
            'teacher_id' => $validated['teacher_id'] ?? null,
            'room_id' => $validated['room_id'] ?? null,
            'room' => $validated['room'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('schedules.index')->with('success', 'Jadwal berhasil diperbarui.');
    }

    public function destroy(Request $request, Schedule $schedule): RedirectResponse
    {
        $school = $this->activeSchool($request);

        if (! $school || $schedule->school_id !== $school->id) {
            abort(403);
        }

        $schedule->delete();

        return redirect()->route('schedules.index')->with('success', 'Jadwal berhasil dihapus.');
    }

    private function rules(int $schoolId): array
    {
        return [
            'academic_year_id' => ['required', Rule::exists('academic_years', 'id')->where('school_id', $schoolId)],
            'semester_id' => ['required', Rule::exists('semesters', 'id')->where('school_id', $schoolId)],
            'classroom_id' => ['required', Rule::exists('classrooms', 'id')->where('school_id', $schoolId)],
            'subject_id' => ['required', Rule::exists('subjects', 'id')->where('school_id', $schoolId)],
            'teacher_id' => ['nullable', Rule::exists('teachers', 'id')->where('school_id', $schoolId)],
            'room_id' => ['nullable', Rule::exists('rooms', 'id')->where('school_id', $schoolId)],
            'day_of_week' => ['required', 'integer', 'between:1,7'],
            'starts_at' => ['required', 'date_format:H:i'],
            'ends_at' => ['required', 'date_format:H:i', 'after:starts_at'],
            'room' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    private function ensureNoConflicts(int $schoolId, array $data, ?int $ignoreId = null): void
    {
        $baseQuery = Schedule::query()
            ->where('school_id', $schoolId)
            ->where('academic_year_id', $data['academic_year_id'])
            ->where('semester_id', $data['semester_id'])
            ->where('day_of_week', $data['day_of_week'])
            ->where('is_active', true)
            ->where('starts_at', '<', $data['ends_at'])
            ->where('ends_at', '>', $data['starts_at'])
            ->when($ignoreId, fn ($query) => $query->whereKeyNot($ignoreId));

        if ((clone $baseQuery)->where('classroom_id', $data['classroom_id'])->exists()) {
            throw ValidationException::withMessages([
                'starts_at' => 'Rombel ini sudah memiliki jadwal pada hari dan jam tersebut.',
            ]);
        }

        if (! empty($data['teacher_id']) && (clone $baseQuery)->where('teacher_id', $data['teacher_id'])->exists()) {
            throw ValidationException::withMessages([
                'teacher_id' => 'Guru ini sudah memiliki jadwal mengajar pada hari dan jam tersebut.',
            ]);
        }

        if (! empty($data['room_id']) && (clone $baseQuery)->where('room_id', $data['room_id'])->exists()) {
            throw ValidationException::withMessages([
                'room_id' => 'Ruangan ini sudah digunakan pada hari dan jam tersebut.',
            ]);
        }
    }

    private function activeSchool(Request $request)
    {
        return EffectiveAccess::school($request);
    }
}
