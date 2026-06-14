<?php

namespace App\Http\Controllers;

use App\Models\AttendanceSession;
use App\Support\EffectiveAccess;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    //
    public function index()
    {
        $user = Auth::user();
        $role = EffectiveAccess::role(request());

        if ($role === 'super_admin') {
            return view('dashboard.super-admin');
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
