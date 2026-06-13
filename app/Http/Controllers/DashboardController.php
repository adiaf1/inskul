<?php

namespace App\Http\Controllers;

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

            if ($school) {
                $school->loadCount(['academicYears', 'semesters', 'classes', 'classrooms', 'rooms', 'schedules', 'subjects', 'teachers', 'students']);
            }

            return view('dashboard.school-admin', compact('school'));
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
