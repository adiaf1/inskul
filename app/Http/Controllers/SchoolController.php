<?php

namespace App\Http\Controllers;

use App\Models\School;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use App\Models\Role;

class SchoolController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->input('status');

        $schools = School::with(['users.roles'])
            ->when($status, fn ($query) => $query->where('status', $status))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('schools.index', compact('schools', 'status'));
    }

    public function approve(School $school): RedirectResponse
    {
        $schoolAdminRoleId = Role::where('name', 'school_admin')->value('id');
        $schoolAdmin = $school->users()
            ->wherePivot('role_id', $schoolAdminRoleId)
            ->first();

        if (! $schoolAdmin) {
            return back()->withErrors('Admin sekolah untuk pengajuan ini tidak ditemukan.');
        }

        $school->update([
            'status' => 'active',
            'approved_at' => now(),
            'approved_by' => Auth::id(),
        ]);

        $schoolAdmin->update(['status' => 'active']);

        $school->users()->updateExistingPivot($schoolAdmin->id, [
            'status' => 'active',
        ]);

        return back()->with('success', 'Sekolah berhasil disetujui dan akun admin sekolah sudah aktif.');
    }

    public function reject(School $school): RedirectResponse
    {
        $school->update([
            'status' => 'rejected',
            'approved_at' => null,
            'approved_by' => null,
        ]);

        foreach ($school->users as $user) {
            $user->update(['status' => 'rejected']);
            $school->users()->updateExistingPivot($user->id, [
                'status' => 'rejected',
            ]);
        }

        return back()->with('success', 'Pengajuan sekolah berhasil ditolak.');
    }
}
