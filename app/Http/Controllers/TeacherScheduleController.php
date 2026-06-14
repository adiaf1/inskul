<?php

namespace App\Http\Controllers;

use App\Models\Teacher;
use App\Support\EffectiveAccess;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TeacherScheduleController extends Controller
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
        $school = EffectiveAccess::school($request);
        $user = EffectiveAccess::user($request);

        if (! $school) {
            return redirect()->route('dashboard')->withErrors('Akun Anda belum terhubung ke sekolah aktif.');
        }

        $teacher = $user
            ? Teacher::query()
                ->where('school_id', $school->id)
                ->where('user_id', $user->id)
                ->where('is_active', true)
                ->first()
            : null;

        if (! $teacher) {
            return redirect()->route('dashboard')->withErrors('Akun Anda belum terhubung ke data guru aktif.');
        }

        $dayOfWeek = $request->input('day_of_week');
        $classroomId = $request->input('classroom_id');

        $schedules = $school->schedules()
            ->with(['academicYear', 'semester', 'classroom', 'subject', 'physicalRoom'])
            ->where('teacher_id', $teacher->id)
            ->where('is_active', true)
            ->when($dayOfWeek, fn ($query) => $query->where('day_of_week', $dayOfWeek))
            ->when($classroomId, fn ($query) => $query->where('classroom_id', $classroomId))
            ->orderBy('day_of_week')
            ->orderBy('starts_at')
            ->get();

        $classrooms = $school->classrooms()
            ->whereHas('schedules', fn ($query) => $query->where('teacher_id', $teacher->id)->where('is_active', true))
            ->orderBy('name')
            ->get();

        return view('teacher-schedules.index', [
            'school' => $school,
            'teacher' => $teacher,
            'schedules' => $schedules,
            'classrooms' => $classrooms,
            'days' => self::DAYS,
            'dayOfWeek' => $dayOfWeek,
            'classroomId' => $classroomId,
        ]);
    }
}
