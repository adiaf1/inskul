<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    //
    public function index()
    {
        $user = Auth::user();

        if ($user->hasRole('super_admin')) {
            return view('dashboard.super-admin');
        } elseif ($user->hasRole('school_admin')) {
            $school = $user->schools()
                ->withCount(['academicYears', 'semesters', 'classes', 'classrooms', 'subjects', 'teachers', 'students'])
                ->wherePivot('status', 'active')
                ->where('schools.status', 'active')
                ->first();

            return view('dashboard.school-admin', compact('school'));
        } elseif ($user->hasRole('principal')) {
            return view('dashboard.principal');
        } elseif ($user->hasRole('teacher')) {
            return view('dashboard.teacher');
        } elseif ($user->hasRole('student')) {
            return view('dashboard.student');
        } elseif ($user->hasRole('parent')) {
            return view('dashboard.parent');
        }

        abort(403, 'Unauthorized');
    }
}
