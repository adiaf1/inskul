<?php

namespace App\Http\Controllers;

use App\Models\AttendanceSession;
use App\Models\Classroom;
use App\Support\EffectiveAccess;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AttendanceController extends Controller
{
    private const RECORD_STATUSES = [
        'present' => 'Hadir',
        'sick' => 'Sakit',
        'permit' => 'Izin',
        'absent' => 'Alpa',
        'late' => 'Terlambat',
    ];

    public function index(Request $request): View|RedirectResponse
    {
        $school = $this->activeSchool($request);

        if (! $school) {
            return redirect()->route('dashboard')->withErrors('Akun Anda belum terhubung ke sekolah aktif.');
        }

        return view('attendances.index', compact('school'));
    }

    public function daily(Request $request): View|RedirectResponse
    {
        $school = $this->activeSchool($request);

        if (! $school) {
            return redirect()->route('dashboard')->withErrors('Akun Anda belum terhubung ke sekolah aktif.');
        }

        $classrooms = $school->classrooms()
            ->with(['academicYear', 'semester'])
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $sessions = $school->attendanceSessions()
            ->with(['classroom', 'teacher.user'])
            ->where('type', 'daily')
            ->latest('attendance_date')
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('attendances.daily', compact('school', 'classrooms', 'sessions'));
    }

    public function openDaily(Request $request): RedirectResponse
    {
        $school = $this->activeSchool($request);

        if (! $school) {
            return redirect()->route('dashboard')->withErrors('Akun Anda belum terhubung ke sekolah aktif.');
        }

        $validated = $request->validate([
            'attendance_date' => ['required', 'date'],
            'classroom_id' => ['required', Rule::exists('classrooms', 'id')->where('school_id', $school->id)],
        ]);

        $classroom = $school->classrooms()
            ->with('homeroomTeacher')
            ->whereKey($validated['classroom_id'])
            ->firstOrFail();

        $session = DB::transaction(function () use ($school, $classroom, $validated) {
            $session = AttendanceSession::firstOrCreate([
                'school_id' => $school->id,
                'classroom_id' => $classroom->id,
                'attendance_date' => $validated['attendance_date'],
                'type' => 'daily',
                'schedule_id' => null,
            ], [
                'teacher_id' => $classroom->homeroom_teacher_id,
                'subject_id' => null,
                'starts_at' => null,
                'ends_at' => null,
                'status' => 'draft',
            ]);

            $this->syncDailyRecords($session, $classroom);

            return $session;
        });

        return redirect()->route('attendances.daily.edit', $session);
    }

    public function editDaily(Request $request, AttendanceSession $attendanceSession): View|RedirectResponse
    {
        $school = $this->activeSchool($request);

        if (! $school || $attendanceSession->school_id !== $school->id || $attendanceSession->type !== 'daily') {
            abort(403);
        }

        $attendanceSession->load(['classroom', 'teacher.user', 'records.student.user']);

        return view('attendances.daily-edit', [
            'school' => $school,
            'session' => $attendanceSession,
            'recordStatuses' => self::RECORD_STATUSES,
        ]);
    }

    public function updateDaily(Request $request, AttendanceSession $attendanceSession): RedirectResponse
    {
        $school = $this->activeSchool($request);

        if (! $school || $attendanceSession->school_id !== $school->id || $attendanceSession->type !== 'daily') {
            abort(403);
        }

        if ($attendanceSession->status === 'locked') {
            return back()->withErrors('Presensi yang sudah dikunci tidak dapat diperbarui.');
        }

        $validated = $request->validate([
            'records' => ['required', 'array'],
            'records.*.status' => ['required', Rule::in(array_keys(self::RECORD_STATUSES))],
            'records.*.notes' => ['nullable', 'string', 'max:1000'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'action' => ['required', Rule::in(['draft', 'submit'])],
        ]);

        DB::transaction(function () use ($attendanceSession, $validated) {
            foreach ($validated['records'] as $recordId => $recordData) {
                $record = $attendanceSession->records()->whereKey($recordId)->first();

                if (! $record) {
                    continue;
                }

                $record->update([
                    'status' => $recordData['status'],
                    'notes' => $recordData['notes'] ?? null,
                    'checked_at' => now(),
                ]);
            }

            $attendanceSession->update([
                'status' => $validated['action'] === 'submit' ? 'submitted' : 'draft',
                'notes' => $validated['notes'] ?? null,
            ]);
        });

        return redirect()
            ->route('attendances.daily.edit', $attendanceSession)
            ->with('success', $validated['action'] === 'submit' ? 'Presensi harian berhasil disubmit.' : 'Draft presensi harian berhasil disimpan.');
    }

    private function syncDailyRecords(AttendanceSession $session, Classroom $classroom): void
    {
        $students = $classroom->students()
            ->wherePivot('status', 'active')
            ->where('students.is_active', true)
            ->get();

        foreach ($students as $student) {
            $session->records()->firstOrCreate([
                'student_id' => $student->id,
            ], [
                'status' => 'present',
                'checked_at' => now(),
            ]);
        }
    }

    private function activeSchool(Request $request)
    {
        return EffectiveAccess::school($request);
    }
}
