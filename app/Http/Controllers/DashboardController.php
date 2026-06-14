<?php

namespace App\Http\Controllers;

use App\Models\AttendanceSession;
use App\Models\AttendanceRecord;
use App\Models\School;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use App\Support\EffectiveAccess;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    //
    public function index()
    {
        $user = Auth::user();
        $role = EffectiveAccess::role(request());

        if ($role === 'super_admin') {
            $schoolStatusCounts = School::query()
                ->select('status', DB::raw('count(*) as total'))
                ->groupBy('status')
                ->pluck('total', 'status');

            $schoolLevelCounts = School::query()
                ->select('level', DB::raw('count(*) as total'))
                ->whereNotNull('level')
                ->groupBy('level')
                ->pluck('total', 'level');

            $summary = [
                'schools_total' => School::count(),
                'schools_active' => (int) ($schoolStatusCounts['active'] ?? 0),
                'schools_pending' => (int) ($schoolStatusCounts['pending'] ?? 0),
                'schools_inactive' => (int) ($schoolStatusCounts['inactive'] ?? 0),
                'schools_rejected' => (int) ($schoolStatusCounts['rejected'] ?? 0),
                'users_total' => User::count(),
                'users_active' => User::where('status', 'active')->count(),
                'teachers_total' => Teacher::count(),
                'students_total' => Student::count(),
                'attendance_today' => AttendanceSession::whereDate('attendance_date', now()->toDateString())->count(),
                'attendance_submitted_today' => AttendanceSession::whereDate('attendance_date', now()->toDateString())
                    ->where('status', 'submitted')
                    ->count(),
            ];

            $pendingSchools = School::query()
                ->with(['users.roles'])
                ->where('status', 'pending')
                ->latest()
                ->limit(5)
                ->get();

            $activeSchools = School::query()
                ->withCount(['teachers', 'students', 'classrooms', 'schedules'])
                ->where('status', 'active')
                ->latest('approved_at')
                ->limit(6)
                ->get();

            return view('dashboard.super-admin', compact(
                'summary',
                'schoolLevelCounts',
                'pendingSchools',
                'activeSchools'
            ));
        } elseif ($role === 'school_admin') {
            $school = EffectiveAccess::school(request());
            $today = now()->toDateString();
            $attendanceSummary = [
                'daily_today' => 0,
                'schedule_today' => 0,
                'submitted_today' => 0,
                'draft_today' => 0,
            ];
            $recentAttendanceSessions = collect();
            $activeAcademicYear = null;
            $activeSemester = null;

            if ($school) {
                $school->loadCount(['academicYears', 'semesters', 'classes', 'classrooms', 'rooms', 'schedules', 'subjects', 'teachers', 'students']);
                $activeAcademicYear = $school->academicYears()->where('is_active', true)->latest()->first();
                $activeSemester = $school->semesters()->where('is_active', true)->latest()->first();

                $attendanceSummary = [
                    'daily_today' => $school->attendanceSessions()->where('type', 'daily')->whereDate('attendance_date', $today)->count(),
                    'schedule_today' => $school->attendanceSessions()->where('type', 'schedule')->whereDate('attendance_date', $today)->count(),
                    'submitted_today' => $school->attendanceSessions()->whereDate('attendance_date', $today)->where('status', 'submitted')->count(),
                    'draft_today' => $school->attendanceSessions()->whereDate('attendance_date', $today)->where('status', 'draft')->count(),
                ];

                $recentAttendanceSessions = AttendanceSession::query()
                    ->with(['classroom', 'subject', 'teacher.user'])
                    ->where('school_id', $school->id)
                    ->latest('attendance_date')
                    ->latest()
                    ->limit(5)
                    ->get();
            }

            return view('dashboard.school-admin', compact(
                'school',
                'attendanceSummary',
                'recentAttendanceSessions',
                'activeAcademicYear',
                'activeSemester'
            ));
        } elseif ($role === 'principal') {
            return view('dashboard.principal');
        } elseif ($role === 'teacher') {
            $school = EffectiveAccess::school(request());
            $effectiveUser = EffectiveAccess::user(request());
            $today = now()->toDateString();
            $todayDay = now()->isoWeekday();
            $teacher = null;
            $todaySchedules = collect();
            $homeroomClassrooms = collect();
            $recentAttendanceSessions = collect();
            $attendanceSummary = [
                'today_total' => 0,
                'today_submitted' => 0,
                'today_draft' => 0,
                'month_total' => 0,
            ];

            if ($school && $effectiveUser) {
                $teacher = Teacher::query()
                    ->with('school')
                    ->where('school_id', $school->id)
                    ->where('user_id', $effectiveUser->id)
                    ->where('is_active', true)
                    ->first();

                if ($teacher) {
                    $todaySchedules = $teacher->schedules()
                        ->with(['classroom', 'subject', 'physicalRoom'])
                        ->where('school_id', $school->id)
                        ->where('is_active', true)
                        ->where('day_of_week', $todayDay)
                        ->orderBy('starts_at')
                        ->get();

                    $homeroomClassrooms = $teacher->homeroomClassrooms()
                        ->with(['academicYear', 'semester'])
                        ->withCount(['students' => fn ($query) => $query->where('classroom_student.status', 'active')->where('students.is_active', true)])
                        ->where('school_id', $school->id)
                        ->where('is_active', true)
                        ->orderBy('name')
                        ->get();

                    $attendanceSummary = [
                        'today_total' => $teacher->attendanceSessions()->whereDate('attendance_date', $today)->count(),
                        'today_submitted' => $teacher->attendanceSessions()->whereDate('attendance_date', $today)->where('status', 'submitted')->count(),
                        'today_draft' => $teacher->attendanceSessions()->whereDate('attendance_date', $today)->where('status', 'draft')->count(),
                        'month_total' => $teacher->attendanceSessions()
                            ->whereBetween('attendance_date', [now()->startOfMonth()->toDateString(), now()->endOfMonth()->toDateString()])
                            ->count(),
                    ];

                    $recentAttendanceSessions = $teacher->attendanceSessions()
                        ->with(['classroom', 'subject'])
                        ->latest('attendance_date')
                        ->latest()
                        ->limit(5)
                        ->get();
                }
            }

            return view('dashboard.teacher', compact(
                'school',
                'teacher',
                'todaySchedules',
                'homeroomClassrooms',
                'recentAttendanceSessions',
                'attendanceSummary'
            ));
        } elseif ($role === 'student') {
            $school = EffectiveAccess::school(request());
            $effectiveUser = EffectiveAccess::user(request());
            $today = now()->toDateString();
            $todayDay = now()->isoWeekday();
            $student = null;
            $activeClassrooms = collect();
            $todaySchedules = collect();
            $todayAttendanceRecords = collect();
            $recentAttendanceRecords = collect();
            $attendanceSummary = [
                'month_present' => 0,
                'month_sick' => 0,
                'month_permit' => 0,
                'month_absent' => 0,
                'month_late' => 0,
            ];

            if ($school && $effectiveUser) {
                $student = Student::query()
                    ->with('school')
                    ->where('school_id', $school->id)
                    ->where('user_id', $effectiveUser->id)
                    ->where('is_active', true)
                    ->first();

                if ($student) {
                    $activeClassrooms = $student->classrooms()
                        ->with(['academicYear', 'semester', 'homeroomTeacher.user'])
                        ->wherePivot('status', 'active')
                        ->where('classrooms.is_active', true)
                        ->orderByDesc('classroom_student.created_at')
                        ->get();

                    $todaySchedules = $school->schedules()
                        ->with(['classroom', 'subject', 'teacher.user', 'physicalRoom'])
                        ->whereIn('classroom_id', $activeClassrooms->pluck('id'))
                        ->where('is_active', true)
                        ->where('day_of_week', $todayDay)
                        ->orderBy('starts_at')
                        ->get();

                    $todayAttendanceRecords = $student->attendanceRecords()
                        ->with(['session.classroom', 'session.subject', 'session.teacher.user'])
                        ->whereHas('session', fn ($query) => $query->whereDate('attendance_date', $today))
                        ->get()
                        ->sortBy(fn ($record) => ($record->session?->type ?? '').($record->session?->starts_at ?? ''));

                    $monthRecords = $student->attendanceRecords()
                        ->whereHas('session', fn ($query) => $query->whereBetween('attendance_date', [
                            now()->startOfMonth()->toDateString(),
                            now()->endOfMonth()->toDateString(),
                        ]))
                        ->get();

                    $attendanceSummary = [
                        'month_present' => $monthRecords->where('status', 'present')->count(),
                        'month_sick' => $monthRecords->where('status', 'sick')->count(),
                        'month_permit' => $monthRecords->where('status', 'permit')->count(),
                        'month_absent' => $monthRecords->where('status', 'absent')->count(),
                        'month_late' => $monthRecords->where('status', 'late')->count(),
                    ];

                    $recentAttendanceRecords = $student->attendanceRecords()
                        ->with(['session.classroom', 'session.subject', 'session.teacher.user'])
                        ->whereHas('session')
                        ->latest('checked_at')
                        ->latest()
                        ->limit(6)
                        ->get()
                        ->sortByDesc(fn ($record) => $record->session?->attendance_date?->timestamp ?? 0);
                }
            }

            return view('dashboard.student', compact(
                'school',
                'student',
                'activeClassrooms',
                'todaySchedules',
                'todayAttendanceRecords',
                'recentAttendanceRecords',
                'attendanceSummary'
            ));
        } elseif ($role === 'parent') {
            return view('dashboard.parent');
        }

        abort(403, 'Unauthorized');
    }
}
