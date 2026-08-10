<?php

namespace App\Http\Controllers;

use App\Models\AcademicCalendarEvent;
use App\Support\EffectiveAccess;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AcademicCalendarEventController extends Controller
{
    public const TYPES = [
        'national_holiday' => 'Libur Nasional',
        'semester_break' => 'Libur Semester',
        'exam' => 'Ujian',
        'school_event' => 'Kegiatan Sekolah',
        'teacher_workday' => 'Hari Kerja Guru',
        'other' => 'Lainnya',
    ];

    public const ATTENDANCE_EFFECTS = [
        'inherit' => 'Ikuti Aturan Normal',
        'attendance_day' => 'Hitung Presensi',
        'non_attendance_day' => 'Tidak Dihitung Presensi',
    ];

    public function index(Request $request): View|RedirectResponse
    {
        $school = $this->activeSchool($request);

        if (! $school) {
            return redirect()->route('dashboard')->withErrors('Akun Anda belum terhubung ke sekolah aktif.');
        }

        $filters = [
            'date_from' => $request->input('date_from', now()->startOfMonth()->format('Y-m-d')),
            'date_to' => $request->input('date_to', now()->endOfMonth()->format('Y-m-d')),
            'type' => $request->input('type', ''),
            'attendance_effect' => $request->input('attendance_effect', ''),
        ];

        $events = $school->academicCalendarEvents()
            ->with(['academicYear', 'semester'])
            ->when($filters['date_from'], fn ($query) => $query->whereDate('ends_at', '>=', $filters['date_from']))
            ->when($filters['date_to'], fn ($query) => $query->whereDate('starts_at', '<=', $filters['date_to']))
            ->when($filters['type'], fn ($query) => $query->where('type', $filters['type']))
            ->when($filters['attendance_effect'], fn ($query) => $query->where('attendance_effect', $filters['attendance_effect']))
            ->orderBy('starts_at')
            ->orderBy('title')
            ->paginate(12)
            ->withQueryString();

        return view('academic-calendar-events.index', [
            'school' => $school,
            'events' => $events,
            'academicYears' => $school->academicYears()->orderByDesc('is_active')->orderBy('name')->get(),
            'semesters' => $school->semesters()->with('academicYear')->orderByDesc('is_active')->orderByDesc('starts_at')->get(),
            'filters' => $filters,
            'types' => self::TYPES,
            'attendanceEffects' => self::ATTENDANCE_EFFECTS,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $school = $this->activeSchool($request);

        if (! $school) {
            return redirect()->route('dashboard')->withErrors('Akun Anda belum terhubung ke sekolah aktif.');
        }

        $validated = $request->validate($this->rules($school->id));

        $school->academicCalendarEvents()->create($validated);

        return redirect()->route('academic-calendar-events.index')->with('success', 'Kalender akademik berhasil dibuat.');
    }

    public function update(Request $request, AcademicCalendarEvent $academicCalendarEvent): RedirectResponse
    {
        $school = $this->activeSchool($request);

        if (! $school || $academicCalendarEvent->school_id !== $school->id) {
            abort(403);
        }

        $validated = $request->validate($this->rules($school->id));

        $academicCalendarEvent->update($validated);

        return redirect()->route('academic-calendar-events.index')->with('success', 'Kalender akademik berhasil diperbarui.');
    }

    public function destroy(Request $request, AcademicCalendarEvent $academicCalendarEvent): RedirectResponse
    {
        $school = $this->activeSchool($request);

        if (! $school || $academicCalendarEvent->school_id !== $school->id) {
            abort(403);
        }

        $academicCalendarEvent->delete();

        return redirect()->route('academic-calendar-events.index')->with('success', 'Kalender akademik berhasil dihapus.');
    }

    private function rules(string $schoolId): array
    {
        return [
            'academic_year_id' => [
                'nullable',
                Rule::exists('academic_years', 'id')->where('school_id', $schoolId),
            ],
            'semester_id' => [
                'nullable',
                Rule::exists('semesters', 'id')->where('school_id', $schoolId),
            ],
            'title' => ['required', 'string', 'max:255'],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['required', 'date', 'after_or_equal:starts_at'],
            'type' => ['required', Rule::in(array_keys(self::TYPES))],
            'attendance_effect' => ['required', Rule::in(array_keys(self::ATTENDANCE_EFFECTS))],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    private function activeSchool(Request $request)
    {
        return EffectiveAccess::school($request);
    }
}
