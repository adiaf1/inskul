<?php

namespace App\Http\Controllers;

use App\Models\Teacher;
use App\Models\TeacherDailyAttendance;
use App\Support\EffectiveAccess;
use App\Support\SchoolFileStorage;
use Carbon\CarbonImmutable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TeacherAttendanceController extends Controller
{
    public function index(Request $request): View|RedirectResponse
    {
        $school = EffectiveAccess::school($request);
        $user = EffectiveAccess::user($request);

        if (! $school || ! $user) {
            return redirect()->route('dashboard')->withErrors('Akun Anda belum terhubung ke sekolah aktif.');
        }

        $teacher = Teacher::query()
            ->where('school_id', $school->id)
            ->where('user_id', $user->id)
            ->where('is_active', true)
            ->first();

        if (! $teacher) {
            return redirect()->route('dashboard')->withErrors('Akun Anda belum terhubung ke data guru aktif.');
        }

        $today = today();
        $attendance = TeacherDailyAttendance::query()
            ->where('school_id', $school->id)
            ->where('teacher_id', $teacher->id)
            ->whereDate('attendance_date', $today)
            ->first();

        $history = TeacherDailyAttendance::query()
            ->where('school_id', $school->id)
            ->where('teacher_id', $teacher->id)
            ->latest('attendance_date')
            ->limit(7)
            ->get();

        return view('teacher-attendances.index', compact('school', 'teacher', 'attendance', 'history'));
    }

    public function store(Request $request): RedirectResponse
    {
        $school = EffectiveAccess::school($request);
        $user = EffectiveAccess::user($request);

        if (! $school || ! $user) {
            return back()->withErrors('Akun Anda belum terhubung ke sekolah aktif.');
        }

        $teacher = Teacher::query()
            ->where('school_id', $school->id)
            ->where('user_id', $user->id)
            ->where('is_active', true)
            ->first();

        if (! $teacher) {
            return back()->withErrors('Akun Anda belum terhubung ke data guru aktif.');
        }

        $validated = $request->validate([
            'type' => ['required', 'in:check_in,check_out'],
            'photo' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'accuracy' => ['nullable', 'numeric', 'min:0', 'max:50000'],
        ]);

        $attendance = TeacherDailyAttendance::query()->firstOrCreate([
            'school_id' => $school->id,
            'teacher_id' => $teacher->id,
            'attendance_date' => today(),
        ]);

        if ($validated['type'] === 'check_in' && $attendance->check_in_at) {
            return back()->withErrors('Anda sudah melakukan presensi datang hari ini.');
        }

        if ($validated['type'] === 'check_out') {
            if (! $attendance->check_in_at) {
                return back()->withErrors('Presensi datang harus dilakukan sebelum presensi pulang.');
            }

            if ($attendance->check_out_at) {
                return back()->withErrors('Anda sudah melakukan presensi pulang hari ini.');
            }
        }

        $now = now();
        $distance = $this->distanceMeters(
            (float) $school->teacher_attendance_latitude,
            (float) $school->teacher_attendance_longitude,
            (float) $validated['latitude'],
            (float) $validated['longitude']
        );
        $accuracy = isset($validated['accuracy']) ? (int) round((float) $validated['accuracy']) : null;
        $status = $this->statusFor($school, $validated['type'], $now, $distance, $accuracy);
        $photoPath = SchoolFileStorage::store($request->file('photo'), $school, 'teacher-attendances', $validated['type'].'-selfie');

        if ($validated['type'] === 'check_in') {
            $attendance->update([
                'check_in_at' => $now,
                'check_in_photo_path' => $photoPath,
                'check_in_latitude' => $validated['latitude'],
                'check_in_longitude' => $validated['longitude'],
                'check_in_accuracy' => $accuracy,
                'check_in_distance_meters' => $distance,
                'check_in_status' => $status,
            ]);
        } else {
            $attendance->update([
                'check_out_at' => $now,
                'check_out_photo_path' => $photoPath,
                'check_out_latitude' => $validated['latitude'],
                'check_out_longitude' => $validated['longitude'],
                'check_out_accuracy' => $accuracy,
                'check_out_distance_meters' => $distance,
                'check_out_status' => $status,
            ]);
        }

        return redirect()
            ->route('teacher-attendances.index')
            ->with('success', 'Presensi '.($validated['type'] === 'check_in' ? 'datang' : 'pulang').' berhasil disimpan.');
    }

    public function report(Request $request): View|RedirectResponse
    {
        $school = EffectiveAccess::school($request);

        if (! $school) {
            return redirect()->route('dashboard')->withErrors('Akun Anda belum terhubung ke sekolah aktif.');
        }

        $date = CarbonImmutable::parse($request->input('date', today()->toDateString()))->toDateString();
        $teacherId = $request->input('teacher_id');

        $teachers = $school->teachers()->with('user')->where('is_active', true)->get()->sortBy('user.name');
        $records = TeacherDailyAttendance::query()
            ->with('teacher.user')
            ->where('school_id', $school->id)
            ->whereDate('attendance_date', $date)
            ->when($teacherId, fn ($query) => $query->where('teacher_id', $teacherId))
            ->latest('check_in_at')
            ->paginate(15)
            ->withQueryString();

        return view('teacher-attendances.report', compact('school', 'teachers', 'records', 'date', 'teacherId'));
    }

    private function statusFor($school, string $type, $time, ?int $distance, ?int $accuracy): string
    {
        if (! $school->teacher_attendance_latitude || ! $school->teacher_attendance_longitude) {
            return 'perlu_verifikasi';
        }

        if ($distance !== null && $distance > (int) ($school->teacher_attendance_radius_meters ?? 150)) {
            return 'di_luar_area';
        }

        if ($accuracy !== null && $accuracy > (int) ($school->teacher_attendance_max_accuracy_meters ?? 200)) {
            return 'perlu_verifikasi';
        }

        $target = CarbonImmutable::parse($time->toDateString().' '.($type === 'check_in'
            ? ($school->teacher_check_in_time ?? '07:00:00')
            : ($school->teacher_check_out_time ?? '14:00:00')));

        if ($type === 'check_in') {
            return $time->greaterThan($target->addMinutes((int) ($school->teacher_late_tolerance_minutes ?? 10)))
                ? 'terlambat'
                : 'hadir';
        }

        return $time->lessThan($target->subMinutes((int) ($school->teacher_early_leave_tolerance_minutes ?? 0)))
            ? 'pulang_cepat'
            : 'pulang';
    }

    private function distanceMeters(float $lat1, float $lon1, float $lat2, float $lon2): ?int
    {
        if ($lat1 == 0.0 && $lon1 == 0.0) {
            return null;
        }

        $earthRadius = 6371000;
        $latFrom = deg2rad($lat1);
        $latTo = deg2rad($lat2);
        $latDelta = deg2rad($lat2 - $lat1);
        $lonDelta = deg2rad($lon2 - $lon1);

        $angle = 2 * asin(sqrt(sin($latDelta / 2) ** 2 + cos($latFrom) * cos($latTo) * sin($lonDelta / 2) ** 2));

        return (int) round($angle * $earthRadius);
    }
}
