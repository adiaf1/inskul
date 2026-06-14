<?php

namespace App\Http\Controllers;

use App\Models\AttendanceSession;
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
            return view('dashboard.teacher');
        } elseif ($role === 'student') {
            return view('dashboard.student');
        } elseif ($role === 'parent') {
            return view('dashboard.parent');
        }

        abort(403, 'Unauthorized');
    }
}
