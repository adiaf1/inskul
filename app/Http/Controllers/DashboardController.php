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

        if ($user->hasRole('admin')) {
            return view('dashboard.admin');
        } elseif ($user->hasRole('editor')) {
            return view('dashboard.editor');
        } elseif ($user->hasRole('guest')) {
            return view('dashboard.guest');
        }

        abort(403, 'Unauthorized');
    }
}
