<?php

namespace App\Http\Controllers;

use App\Models\AcademicCalendarEvent;
use App\Models\AttendanceSession;
use App\Models\AttendanceRecord;
use App\Models\Classroom;
use App\Models\Student;
use App\Models\StudentDailyAttendance;
use App\Models\Teacher;
use App\Support\EffectiveAccess;
use Illuminate\Support\Carbon;
use Illuminate\Http\JsonResponse;
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

    private const DAILY_MANUAL_STATUSES = [
        'sick' => 'Sakit',
        'permit' => 'Izin',
        'absent' => 'Alpa',
    ];

    public function index(Request $request): View|RedirectResponse
    {
        $school = $this->activeSchool($request);

        if (! $school) {
            return redirect()->route('dashboard')->withErrors('Akun Anda belum terhubung ke sekolah aktif.');
        }

        return view('attendances.index', compact('school'));
    }

    public function check(Request $request): View|RedirectResponse
    {
        $school = $this->activeSchool($request);

        if (! $school) {
            return redirect()->route('dashboard')->withErrors('Akun Anda belum terhubung ke sekolah aktif.');
        }

        if ($this->effectiveRole($request) === 'student') {
            abort(403);
        }

        $today = now()->toDateString();

        $todayAttendances = StudentDailyAttendance::query()
            ->with(['student.user', 'checkInBy', 'checkOutBy'])
            ->where('school_id', $school->id)
            ->whereDate('attendance_date', $today)
            ->latest('updated_at')
            ->limit(20)
            ->get();
        $activeStudents = Student::query()
            ->with(['user', 'classrooms' => fn ($query) => $query->wherePivot('status', 'active')->where('classrooms.is_active', true)])
            ->where('school_id', $school->id)
            ->where('is_active', true)
            ->whereDoesntHave('dailyAttendances', fn ($query) => $query->whereDate('attendance_date', $today))
            ->join('users', 'students.user_id', '=', 'users.id')
            ->orderBy('users.name')
            ->select('students.*')
            ->get();
        $attendanceDayContext = $this->attendanceDayContext($school, now());
        $isAttendanceDay = $attendanceDayContext['is_attendance_day'];
        $schoolAttendanceDayLabels = $this->schoolAttendanceDayLabels($school);

        return view('attendances.check', compact('school', 'todayAttendances', 'activeStudents', 'isAttendanceDay', 'schoolAttendanceDayLabels', 'attendanceDayContext'));
    }

    public function scanCheck(Request $request): JsonResponse
    {
        $school = $this->activeSchool($request);

        if (! $school) {
            abort(403);
        }

        if ($this->effectiveRole($request) === 'student') {
            abort(403);
        }

        $attendanceDayContext = $this->attendanceDayContext($school, now());

        if (! $attendanceDayContext['is_attendance_day']) {
            return response()->json([
                'message' => 'Hari ini bukan hari presensi aktif. '.$attendanceDayContext['message'],
            ], 422);
        }

        $validated = $request->validate([
            'student_id' => ['required', 'string', 'max:255'],
        ]);

        $studentId = $this->extractUuid($validated['student_id']);

        if (! $studentId) {
            return response()->json([
                'message' => 'QR Code tidak valid. Pastikan QR berasal dari nametag murid.',
            ], 422);
        }

        $student = Student::query()
            ->with(['user', 'classrooms' => fn ($query) => $query->wherePivot('status', 'active')->where('classrooms.is_active', true)])
            ->where('school_id', $school->id)
            ->where('is_active', true)
            ->whereKey($studentId)
            ->first();

        if (! $student) {
            return response()->json([
                'message' => 'QR Code terbaca, tetapi data tersebut bukan nametag murid aktif pada sekolah ini.',
            ], 404);
        }

        $actorId = $request->user()?->id;
        $scanAt = now();
        $today = $scanAt->toDateString();
        $action = 'complete';
        $canCheckoutAfter = null;

        $attendance = DB::transaction(function () use ($school, $student, $actorId, $today, $scanAt, &$action, &$canCheckoutAfter) {
            $attendance = StudentDailyAttendance::query()
                ->where('school_id', $school->id)
                ->where('student_id', $student->id)
                ->whereDate('attendance_date', $today)
                ->lockForUpdate()
                ->first();

            if (! $attendance) {
                $action = 'check_in';
                $checkInEvaluation = $this->dailyCheckInEvaluation($school, $scanAt);

                return StudentDailyAttendance::create([
                    'school_id' => $school->id,
                    'student_id' => $student->id,
                    'attendance_date' => $today,
                    'check_in_at' => $scanAt,
                    'check_in_by' => $actorId,
                    'check_in_status' => $checkInEvaluation['status'],
                    'late_minutes' => $checkInEvaluation['late_minutes'],
                    'status' => 'open',
                ]);
            }

            if (! $attendance->check_in_at) {
                $action = 'check_in';
                $checkInEvaluation = $this->dailyCheckInEvaluation($school, $scanAt);

                $attendance->update([
                    'check_in_at' => $scanAt,
                    'check_in_by' => $actorId,
                    'check_in_status' => $checkInEvaluation['status'],
                    'late_minutes' => $checkInEvaluation['late_minutes'],
                    'check_out_at' => null,
                    'check_out_by' => null,
                    'check_out_status' => null,
                    'early_leave_minutes' => 0,
                    'status' => 'open',
                    'notes' => null,
                ]);

                return $attendance;
            }

            if (! $attendance->check_out_at) {
                $minCheckoutMinutes = (int) ($school->daily_min_checkout_minutes ?? 60);
                $canCheckoutAfter = $attendance->check_in_at->copy()->addMinutes($minCheckoutMinutes);

                if ($scanAt->lt($canCheckoutAfter)) {
                    $action = 'duplicate_check_in';

                    return $attendance;
                }

                $action = 'check_out';
                $checkOutEvaluation = $this->dailyCheckOutEvaluation($school, $scanAt);

                $attendance->update([
                    'check_out_at' => $scanAt,
                    'check_out_by' => $actorId,
                    'check_out_status' => $checkOutEvaluation['status'],
                    'early_leave_minutes' => $checkOutEvaluation['early_leave_minutes'],
                    'status' => 'closed',
                ]);

                return $attendance;
            }

            return $attendance;
        });

        $attendance->load(['student.user', 'checkInBy', 'checkOutBy']);
        $studentName = $student->user?->name ?? 'Murid';
        $checkInStatusLabels = $this->dailyCheckInStatusLabels();
        $checkOutStatusLabels = $this->dailyCheckOutStatusLabels();

        return response()->json([
            'message' => match ($action) {
                'check_in' => $attendance->check_in_status === 'late'
                    ? $studentName.' berhasil dicatat datang terlambat '.$attendance->late_minutes.' menit.'
                    : $studentName.' berhasil dicatat datang tepat waktu.',
                'check_out' => $attendance->check_out_status === 'early'
                    ? $studentName.' berhasil dicatat pulang cepat '.$attendance->early_leave_minutes.' menit.'
                    : $studentName.' berhasil dicatat pulang.',
                'duplicate_check_in' => $studentName.' sudah tercatat datang pukul '.$attendance->check_in_at?->format('H:i:s').'. Pulang baru dapat dicatat setelah '.$canCheckoutAfter?->format('H:i:s').'.',
                default => $studentName.' sudah tercatat datang dan pulang hari ini.',
            },
            'action' => $action,
            'student' => [
                'id' => $student->id,
                'name' => $studentName,
                'nis' => $student->nis,
                'nisn' => $student->nisn,
                'classroom' => $student->classrooms->first()?->name,
            ],
            'attendance' => [
                'id' => $attendance->id,
                'attendance_date' => $attendance->attendance_date?->format('d M Y'),
                'check_in_at' => $attendance->check_in_at?->format('H:i:s'),
                'check_out_at' => $attendance->check_out_at?->format('H:i:s'),
                'check_in_status' => $attendance->check_in_status,
                'check_in_status_label' => $checkInStatusLabels[$attendance->check_in_status] ?? null,
                'late_minutes' => $attendance->late_minutes,
                'check_out_status' => $attendance->check_out_status,
                'check_out_status_label' => $checkOutStatusLabels[$attendance->check_out_status] ?? null,
                'early_leave_minutes' => $attendance->early_leave_minutes,
                'status' => $attendance->status,
                'can_checkout_after' => $canCheckoutAfter?->format('H:i:s'),
            ],
        ]);
    }

    public function manualCheck(Request $request): JsonResponse
    {
        $school = $this->activeSchool($request);

        if (! $school) {
            abort(403);
        }

        if ($this->effectiveRole($request) === 'student') {
            abort(403);
        }

        $attendanceDayContext = $this->attendanceDayContext($school, now());

        if (! $attendanceDayContext['is_attendance_day']) {
            return response()->json([
                'message' => 'Hari ini bukan hari presensi aktif. '.$attendanceDayContext['message'],
            ], 422);
        }

        $validated = $request->validate([
            'student_id' => ['required', 'string', 'exists:students,id'],
            'status' => ['required', Rule::in(array_keys(self::DAILY_MANUAL_STATUSES))],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $student = Student::query()
            ->with(['user', 'classrooms' => fn ($query) => $query->wherePivot('status', 'active')->where('classrooms.is_active', true)])
            ->where('school_id', $school->id)
            ->where('is_active', true)
            ->whereKey($validated['student_id'])
            ->first();

        if (! $student) {
            return response()->json([
                'message' => 'Murid tidak ditemukan atau tidak aktif pada sekolah ini.',
            ], 404);
        }

        $today = now()->toDateString();
        $status = $validated['status'];

        $attendance = DB::transaction(function () use ($school, $student, $today, $status, $validated) {
            $attendance = StudentDailyAttendance::query()
                ->where('school_id', $school->id)
                ->where('student_id', $student->id)
                ->whereDate('attendance_date', $today)
                ->lockForUpdate()
                ->first();

            if ($attendance && ($attendance->check_in_at || $attendance->check_out_at)) {
                return $attendance;
            }

            $payload = [
                'school_id' => $school->id,
                'student_id' => $student->id,
                'attendance_date' => $today,
                'check_in_at' => null,
                'check_in_by' => null,
                'check_in_status' => null,
                'late_minutes' => 0,
                'check_out_at' => null,
                'check_out_by' => null,
                'check_out_status' => null,
                'early_leave_minutes' => 0,
                'status' => $status,
                'notes' => $validated['notes'] ?? null,
            ];

            if ($attendance) {
                $attendance->update($payload);

                return $attendance;
            }

            return StudentDailyAttendance::create($payload);
        });

        if ($attendance->check_in_at || $attendance->check_out_at) {
            return response()->json([
                'message' => ($student->user?->name ?? 'Murid').' sudah tercatat hadir hari ini. Status sakit/izin/alpa tidak diterapkan.',
            ], 422);
        }

        $attendance->load('student.user');
        $statusLabel = self::DAILY_MANUAL_STATUSES[$attendance->status] ?? $attendance->status;

        return response()->json([
            'message' => ($student->user?->name ?? 'Murid').' ditandai '.$statusLabel.'.',
            'action' => 'manual_status',
            'student' => [
                'id' => $student->id,
                'name' => $student->user?->name ?? 'Murid',
                'nis' => $student->nis,
                'nisn' => $student->nisn,
                'classroom' => $student->classrooms->first()?->name,
            ],
            'attendance' => [
                'id' => $attendance->id,
                'attendance_date' => $attendance->attendance_date?->format('d M Y'),
                'check_in_at' => null,
                'check_out_at' => null,
                'status' => $attendance->status,
                'status_label' => $statusLabel,
                'notes' => $attendance->notes,
            ],
        ]);
    }

    public function dailyDashboard(Request $request): View|RedirectResponse
    {
        $school = $this->activeSchool($request);

        if (! $school) {
            return redirect()->route('dashboard')->withErrors('Akun Anda belum terhubung ke sekolah aktif.');
        }

        if (! in_array($this->effectiveRole($request), ['school_admin', 'principal'], true)) {
            abort(403);
        }

        $validated = $request->validate([
            'date' => ['nullable', 'date'],
            'classroom_id' => ['nullable', 'string'],
        ]);

        $filters = [
            'date' => $validated['date'] ?? now()->toDateString(),
            'classroom_id' => $validated['classroom_id'] ?? '',
        ];
        $selectedDateCarbon = Carbon::parse($filters['date']);
        $selectedDate = $selectedDateCarbon->toDateString();
        $attendanceDayContext = $this->attendanceDayContext($school, $selectedDateCarbon);
        $isAttendanceDay = $attendanceDayContext['is_attendance_day'];

        $classrooms = $school->classrooms()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $selectedClassroom = $filters['classroom_id']
            ? $classrooms->firstWhere('id', $filters['classroom_id'])
            : null;

        if ($filters['classroom_id'] && ! $selectedClassroom) {
            return redirect()
                ->route('attendances.daily-dashboard')
                ->withErrors('Rombel yang dipilih tidak ditemukan pada sekolah aktif.');
        }

        $studentQuery = $this->dailyDashboardStudentQuery($school, $filters['classroom_id']);
        $studentIds = (clone $studentQuery)->pluck('students.id');
        $totalStudents = $studentIds->count();

        $attendanceRows = $studentIds->isEmpty()
            ? collect()
            : StudentDailyAttendance::query()
                ->where('school_id', $school->id)
                ->whereDate('attendance_date', $selectedDate)
                ->whereIn('student_id', $studentIds)
                ->get();

        $presentCount = $attendanceRows->whereNotNull('check_in_at')->pluck('student_id')->unique()->count();
        $manualStatusStudentIds = fn (string $status) => $attendanceRows
            ->filter(fn ($attendance) => ! $attendance->check_in_at && $attendance->status === $status)
            ->pluck('student_id')
            ->unique()
            ->count();
        $sickCount = $manualStatusStudentIds('sick');
        $permitCount = $manualStatusStudentIds('permit');
        $absentCount = $manualStatusStudentIds('absent');
        $processedCount = $presentCount + $sickCount + $permitCount + $absentCount;
        $unprocessedCount = $isAttendanceDay ? max($totalStudents - $processedCount, 0) : 0;
        $onTimeCount = $attendanceRows->where('check_in_status', 'on_time')->pluck('student_id')->unique()->count();
        $lateCount = $attendanceRows->where('check_in_status', 'late')->pluck('student_id')->unique()->count();
        $checkedOutCount = $attendanceRows->whereNotNull('check_out_at')->pluck('student_id')->unique()->count();
        $earlyLeaveCount = $attendanceRows->where('check_out_status', 'early')->pluck('student_id')->unique()->count();
        $openCount = max($presentCount - $checkedOutCount, 0);
        $attendancePercent = $isAttendanceDay && $totalStudents > 0 ? round(($presentCount / $totalStudents) * 100, 1) : 0;

        $trendStart = Carbon::parse($selectedDate)->subDays(6)->startOfDay();
        $trendEnd = Carbon::parse($selectedDate)->endOfDay();
        $trendRows = $studentIds->isEmpty()
            ? collect()
            : StudentDailyAttendance::query()
                ->selectRaw('DATE(attendance_date) as attendance_day')
                ->selectRaw('COUNT(DISTINCT CASE WHEN check_in_at IS NOT NULL THEN student_id END) as present_count')
                ->selectRaw('COUNT(DISTINCT CASE WHEN check_in_status = ? THEN student_id END) as late_count', ['late'])
                ->selectRaw('COUNT(DISTINCT CASE WHEN check_in_at IS NULL AND status = ? THEN student_id END) as sick_count', ['sick'])
                ->selectRaw('COUNT(DISTINCT CASE WHEN check_in_at IS NULL AND status = ? THEN student_id END) as permit_count', ['permit'])
                ->selectRaw('COUNT(DISTINCT CASE WHEN check_in_at IS NULL AND status = ? THEN student_id END) as absent_count', ['absent'])
                ->where('school_id', $school->id)
                ->whereBetween('attendance_date', [$trendStart->toDateString(), $trendEnd->toDateString()])
                ->whereIn('student_id', $studentIds)
                ->groupByRaw('DATE(attendance_date)')
                ->get()
                ->keyBy('attendance_day');

        $trendLabels = [];
        $trendPresent = [];
        $trendLate = [];
        $trendSick = [];
        $trendPermit = [];
        $trendAbsent = [];
        $trendUnprocessed = [];

        for ($date = $trendStart->copy(); $date->lte($trendEnd); $date->addDay()) {
            $key = $date->toDateString();
            $row = $trendRows->get($key);
            $present = (int) ($row?->present_count ?? 0);
            $late = (int) ($row?->late_count ?? 0);
            $sick = (int) ($row?->sick_count ?? 0);
            $permit = (int) ($row?->permit_count ?? 0);
            $absent = (int) ($row?->absent_count ?? 0);
            $processed = $present + $sick + $permit + $absent;

            $trendLabels[] = $date->format('d M');
            $trendPresent[] = $present;
            $trendLate[] = $late;
            $trendSick[] = $sick;
            $trendPermit[] = $permit;
            $trendAbsent[] = $absent;
            $trendUnprocessed[] = $this->attendanceDayContext($school, $date)['is_attendance_day'] ? max($totalStudents - $processed, 0) : 0;
        }

        $students = $this->dailyDashboardStudentQuery($school, $filters['classroom_id'])
            ->with(['user', 'classrooms' => fn ($query) => $query->wherePivot('status', 'active')->where('classrooms.is_active', true)])
            ->join('users', 'students.user_id', '=', 'users.id')
            ->orderBy('users.name')
            ->select('students.*')
            ->paginate(25)
            ->withQueryString();

        $studentAttendances = $students->getCollection()->isEmpty()
            ? collect()
            : StudentDailyAttendance::query()
                ->where('school_id', $school->id)
                ->whereDate('attendance_date', $selectedDate)
                ->whereIn('student_id', $students->getCollection()->pluck('id'))
                ->get()
                ->keyBy('student_id');

        return view('attendances.daily-dashboard', [
            'school' => $school,
            'classrooms' => $classrooms,
            'selectedClassroom' => $selectedClassroom,
            'filters' => $filters,
            'students' => $students,
            'studentAttendances' => $studentAttendances,
            'isAttendanceDay' => $isAttendanceDay,
            'attendanceDayContext' => $attendanceDayContext,
            'schoolAttendanceDayLabels' => $this->schoolAttendanceDayLabels($school),
            'summary' => [
                'total_students' => $totalStudents,
                'present' => $presentCount,
                'sick' => $sickCount,
                'permit' => $permitCount,
                'absent' => $absentCount,
                'unprocessed' => $unprocessedCount,
                'on_time' => $onTimeCount,
                'late' => $lateCount,
                'checked_out' => $checkedOutCount,
                'open' => $openCount,
                'early_leave' => $earlyLeaveCount,
                'attendance_percent' => $attendancePercent,
            ],
            'chartData' => [
                'attendance' => [
                    'labels' => ['Hadir', 'Sakit', 'Izin', 'Alpa', 'Belum Diproses'],
                    'series' => [$presentCount, $sickCount, $permitCount, $absentCount, $unprocessedCount],
                ],
                'punctuality' => [
                    'labels' => ['Tepat Waktu', 'Terlambat'],
                    'series' => [$onTimeCount, $lateCount],
                ],
                'checkout' => [
                    'labels' => ['Sudah Pulang', 'Belum Pulang', 'Pulang Cepat'],
                    'series' => [$checkedOutCount, $openCount, $earlyLeaveCount],
                ],
                'trend' => [
                    'labels' => $trendLabels,
                    'present' => $trendPresent,
                    'late' => $trendLate,
                    'sick' => $trendSick,
                    'permit' => $trendPermit,
                    'absent' => $trendAbsent,
                    'unprocessed' => $trendUnprocessed,
                ],
            ],
        ]);
    }

    public function report(Request $request): View|RedirectResponse
    {
        $school = $this->activeSchool($request);

        if (! $school) {
            return redirect()->route('dashboard')->withErrors('Akun Anda belum terhubung ke sekolah aktif.');
        }

        $filters = $this->reportFilters($request);
        $classrooms = $school->classrooms()->where('is_active', true)->orderBy('name')->get();
        $records = $this->reportRecordsQuery($school, $filters)
            ->paginate(20)
            ->withQueryString();

        return view('attendances.report', [
            'school' => $school,
            'records' => $records,
            'classrooms' => $classrooms,
            'filters' => $filters,
            'recordStatuses' => self::RECORD_STATUSES,
        ]);
    }

    public function printReport(Request $request): View|RedirectResponse
    {
        $school = $this->activeSchool($request);

        if (! $school) {
            return redirect()->route('dashboard')->withErrors('Akun Anda belum terhubung ke sekolah aktif.');
        }

        $filters = $this->reportFilters($request);
        $records = $this->reportRecordsQuery($school, $filters)->get();
        $selectedClassroom = $filters['classroom_id']
            ? $school->classrooms()->find($filters['classroom_id'])
            : null;

        return view('attendances.report-print', [
            'school' => $school,
            'records' => $records,
            'filters' => $filters,
            'selectedClassroom' => $selectedClassroom,
            'recordStatuses' => self::RECORD_STATUSES,
        ]);
    }

    public function dailyReport(Request $request): View|RedirectResponse
    {
        $school = $this->activeSchool($request);

        if (! $school) {
            return redirect()->route('dashboard')->withErrors('Akun Anda belum terhubung ke sekolah aktif.');
        }

        $filters = $this->dailyReportFilters($request);
        $teacher = $this->activeTeacher($request, $school->id);
        $isTeacher = $this->effectiveRole($request) === 'teacher';

        $classrooms = $school->classrooms()
            ->where('is_active', true)
            ->when($isTeacher, fn ($query) => $teacher
                ? $query->where('homeroom_teacher_id', $teacher->id)
                : $query->whereRaw('1 = 0'))
            ->orderBy('name')
            ->get();

        $records = $this->dailyReportRecordsQuery($school, $filters, $teacher, $isTeacher)
            ->paginate(20)
            ->withQueryString();
        $summaryRows = $this->dailyReportRecordsQuery($school, $filters, $teacher, $isTeacher)
            ->get()
            ->groupBy('student_id')
            ->map(function ($studentRecords) {
                $firstRecord = $studentRecords->first();

                return [
                    'student' => $firstRecord->student,
                    'present' => $studentRecords->where('status', 'present')->count(),
                    'sick' => $studentRecords->where('status', 'sick')->count(),
                    'absent' => $studentRecords->where('status', 'absent')->count(),
                    'permit' => $studentRecords->where('status', 'permit')->count(),
                    'late' => $studentRecords->where('status', 'late')->count(),
                ];
            })
            ->sortBy(fn ($row) => $row['student']?->user?->name)
            ->values();

        return view('attendances.daily-report', [
            'school' => $school,
            'records' => $records,
            'summaryRows' => $summaryRows,
            'classrooms' => $classrooms,
            'filters' => $filters,
            'recordStatuses' => self::RECORD_STATUSES,
        ]);
    }

    public function printDailyReport(Request $request): View|RedirectResponse
    {
        $school = $this->activeSchool($request);

        if (! $school) {
            return redirect()->route('dashboard')->withErrors('Akun Anda belum terhubung ke sekolah aktif.');
        }

        $filters = $this->dailyReportFilters($request);
        $teacher = $this->activeTeacher($request, $school->id);
        $isTeacher = $this->effectiveRole($request) === 'teacher';
        $records = $this->dailyReportRecordsQuery($school, $filters, $teacher, $isTeacher)->get();
        $selectedClassroom = $filters['classroom_id']
            ? $school->classrooms()
                ->with(['academicYear', 'semester', 'homeroomTeacher.user'])
                ->find($filters['classroom_id'])
            : null;
        $dateColumns = collect();

        $currentDate = Carbon::parse($filters['date_from']);
        $endDate = Carbon::parse($filters['date_to']);

        while ($currentDate->lte($endDate)) {
            $dateColumns->push($currentDate->copy());
            $currentDate->addDay();
        }

        $studentRows = $records
            ->groupBy('student_id')
            ->map(function ($studentRecords) {
                $firstRecord = $studentRecords->first();

                return [
                    'student' => $firstRecord->student,
                    'records_by_date' => $studentRecords->keyBy(fn ($record) => $record->session?->attendance_date?->format('Y-m-d')),
                    'present' => $studentRecords->where('status', 'present')->count(),
                    'sick' => $studentRecords->where('status', 'sick')->count(),
                    'absent' => $studentRecords->where('status', 'absent')->count(),
                    'permit' => $studentRecords->where('status', 'permit')->count(),
                    'late' => $studentRecords->where('status', 'late')->count(),
                ];
            })
            ->sortBy(fn ($row) => $row['student']?->user?->name)
            ->values();

        return view('attendances.daily-report-print', [
            'school' => $school,
            'records' => $records,
            'filters' => $filters,
            'selectedClassroom' => $selectedClassroom,
            'dateColumns' => $dateColumns,
            'studentRows' => $studentRows,
            'recordStatuses' => self::RECORD_STATUSES,
        ]);
    }

    public function periodReport(Request $request): View|RedirectResponse
    {
        $school = $this->activeSchool($request);

        if (! $school) {
            return redirect()->route('dashboard')->withErrors('Akun Anda belum terhubung ke sekolah aktif.');
        }

        if ($this->effectiveRole($request) !== 'school_admin') {
            abort(403);
        }

        $filters = $this->periodReportFilters($request);
        $report = $this->buildDailyPeriodReport($school, $filters);

        return view('attendances.period-report', [
            'school' => $school,
            'classrooms' => $report['classrooms'],
            'selectedClassroom' => $report['selected_classroom'],
            'filters' => $filters,
            'effectiveDates' => $report['effective_dates'],
            'summaryRows' => $report['summary_rows'],
            'totals' => $report['totals'],
        ]);
    }

    public function printPeriodReport(Request $request): View|RedirectResponse
    {
        $school = $this->activeSchool($request);

        if (! $school) {
            return redirect()->route('dashboard')->withErrors('Akun Anda belum terhubung ke sekolah aktif.');
        }

        if ($this->effectiveRole($request) !== 'school_admin') {
            abort(403);
        }

        $filters = $this->periodReportFilters($request);
        $report = $this->buildDailyPeriodReport($school, $filters);

        return view('attendances.period-report-print', [
            'school' => $school,
            'selectedClassroom' => $report['selected_classroom'],
            'filters' => $filters,
            'effectiveDates' => $report['effective_dates'],
            'summaryRows' => $report['summary_rows'],
            'totals' => $report['totals'],
        ]);
    }

    public function scheduleReport(Request $request): View|RedirectResponse
    {
        $school = $this->activeSchool($request);

        if (! $school) {
            return redirect()->route('dashboard')->withErrors('Akun Anda belum terhubung ke sekolah aktif.');
        }

        $filters = $this->scheduleReportFilters($request);
        $teacher = $this->activeTeacher($request, $school->id);
        $isTeacher = $this->effectiveRole($request) === 'teacher';

        $teacherScheduleQuery = $teacher
            ? $teacher->schedules()
                ->where('school_id', $school->id)
                ->where('is_active', true)
            : null;

        $teacherClassroomIds = $isTeacher && $teacherScheduleQuery
            ? (clone $teacherScheduleQuery)->pluck('classroom_id')->filter()->unique()
            : collect();
        $teacherSubjectIds = $isTeacher && $teacherScheduleQuery
            ? (clone $teacherScheduleQuery)->pluck('subject_id')->filter()->unique()
            : collect();

        $classrooms = $school->classrooms()
            ->where('is_active', true)
            ->when($isTeacher, fn ($query) => $query->whereIn('id', $teacherClassroomIds))
            ->orderBy('name')
            ->get();
        $subjects = $school->subjects()
            ->where('is_active', true)
            ->when($isTeacher, fn ($query) => $query->whereIn('id', $teacherSubjectIds))
            ->orderBy('name')
            ->get();
        $records = $this->scheduleReportRecordsQuery($school, $filters, $teacher, $isTeacher)
            ->paginate(20)
            ->withQueryString();
        $summaryRows = $this->scheduleReportRecordsQuery($school, $filters, $teacher, $isTeacher)
            ->get()
            ->groupBy('student_id')
            ->map(function ($studentRecords) {
                $firstRecord = $studentRecords->first();

                return [
                    'student' => $firstRecord->student,
                    'present' => $studentRecords->where('status', 'present')->count(),
                    'sick' => $studentRecords->where('status', 'sick')->count(),
                    'absent' => $studentRecords->where('status', 'absent')->count(),
                    'permit' => $studentRecords->where('status', 'permit')->count(),
                    'late' => $studentRecords->where('status', 'late')->count(),
                ];
            })
            ->sortBy(fn ($row) => $row['student']?->user?->name)
            ->values();

        return view('attendances.schedule-report', [
            'school' => $school,
            'records' => $records,
            'summaryRows' => $summaryRows,
            'classrooms' => $classrooms,
            'subjects' => $subjects,
            'filters' => $filters,
            'recordStatuses' => self::RECORD_STATUSES,
        ]);
    }

    public function printScheduleReport(Request $request): View|RedirectResponse
    {
        $school = $this->activeSchool($request);

        if (! $school) {
            return redirect()->route('dashboard')->withErrors('Akun Anda belum terhubung ke sekolah aktif.');
        }

        $filters = $this->scheduleReportFilters($request);
        $teacher = $this->activeTeacher($request, $school->id);
        $isTeacher = $this->effectiveRole($request) === 'teacher';
        $records = $this->scheduleReportRecordsQuery($school, $filters, $teacher, $isTeacher)->get();
        $selectedClassroom = $filters['classroom_id']
            ? $school->classrooms()
                ->with(['academicYear', 'semester'])
                ->find($filters['classroom_id'])
            : null;
        $selectedSubject = $filters['subject_id'] ? $school->subjects()->find($filters['subject_id']) : null;
        $selectedTeacher = $isTeacher ? $teacher : null;
        $dateColumns = collect();

        $currentDate = Carbon::parse($filters['date_from']);
        $endDate = Carbon::parse($filters['date_to']);

        while ($currentDate->lte($endDate)) {
            $dateColumns->push($currentDate->copy());
            $currentDate->addDay();
        }

        $subjectSheets = $records
            ->groupBy(fn ($record) => $record->session?->subject_id ?? 'unknown')
            ->map(function ($subjectRecords) {
                $firstRecord = $subjectRecords->first();

                return [
                    'subject' => $firstRecord->session?->subject ?? $firstRecord->session?->schedule?->subject,
                    'teacher' => $firstRecord->session?->teacher,
                    'classroom' => $firstRecord->session?->classroom,
                    'records' => $subjectRecords,
                    'student_rows' => $subjectRecords
                        ->groupBy('student_id')
                        ->map(function ($studentRecords) {
                            $firstRecord = $studentRecords->first();

                            return [
                                'student' => $firstRecord->student,
                                'records_by_date' => $studentRecords->groupBy(fn ($record) => $record->session?->attendance_date?->format('Y-m-d')),
                            ];
                        })
                        ->sortBy(fn ($row) => $row['student']?->user?->name)
                        ->values(),
                ];
            })
            ->sortBy(fn ($sheet) => $sheet['subject']?->name ?? '-')
            ->values();

        return view('attendances.schedule-report-print', [
            'school' => $school,
            'records' => $records,
            'filters' => $filters,
            'selectedClassroom' => $selectedClassroom,
            'selectedSubject' => $selectedSubject,
            'selectedTeacher' => $selectedTeacher,
            'dateColumns' => $dateColumns,
            'subjectSheets' => $subjectSheets,
            'recordStatuses' => self::RECORD_STATUSES,
        ]);
    }

    public function daily(Request $request): View|RedirectResponse
    {
        $school = $this->activeSchool($request);

        if (! $school) {
            return redirect()->route('dashboard')->withErrors('Akun Anda belum terhubung ke sekolah aktif.');
        }

        $teacher = $this->activeTeacher($request, $school->id);
        $isTeacher = $this->effectiveRole($request) === 'teacher';

        if ($isTeacher && ! $teacher) {
            return redirect()->route('attendances.index')->withErrors('Akun guru Anda belum terhubung ke data guru aktif.');
        }

        $classrooms = $school->classrooms()
            ->with(['academicYear', 'semester'])
            ->where('is_active', true)
            ->when($isTeacher, fn ($query) => $query->where('homeroom_teacher_id', $teacher->id))
            ->orderBy('name')
            ->get();

        $sessions = $school->attendanceSessions()
            ->with(['classroom', 'teacher.user'])
            ->where('type', 'daily')
            ->when($isTeacher, fn ($query) => $query->whereIn('classroom_id', $classrooms->pluck('id')))
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

        $teacher = $this->activeTeacher($request, $school->id);
        $isTeacher = $this->effectiveRole($request) === 'teacher';

        $validated = $request->validate([
            'attendance_date' => ['required', 'date'],
            'classroom_id' => ['required', Rule::exists('classrooms', 'id')->where('school_id', $school->id)],
        ]);

        $classroom = $school->classrooms()
            ->with('homeroomTeacher')
            ->whereKey($validated['classroom_id'])
            ->firstOrFail();

        if ($isTeacher && (! $teacher || $classroom->homeroom_teacher_id !== $teacher->id)) {
            return back()
                ->withInput()
                ->withErrors('Guru hanya dapat membuka presensi per kelas untuk rombel yang menjadi wali kelasnya.');
        }

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

    public function schedule(Request $request): View|RedirectResponse
    {
        $school = $this->activeSchool($request);

        if (! $school) {
            return redirect()->route('dashboard')->withErrors('Akun Anda belum terhubung ke sekolah aktif.');
        }

        $teacher = $this->activeTeacher($request, $school->id);
        $isTeacher = $this->effectiveRole($request) === 'teacher';

        if ($isTeacher && ! $teacher) {
            return redirect()->route('attendances.index')->withErrors('Akun guru Anda belum terhubung ke data guru aktif.');
        }

        $attendanceDate = $request->input('attendance_date', now()->format('Y-m-d'));
        $selectedDay = Carbon::parse($attendanceDate)->isoWeekday();

        $schedules = $school->schedules()
            ->with(['classroom', 'subject', 'teacher.user', 'physicalRoom'])
            ->where('is_active', true)
            ->where('day_of_week', $selectedDay)
            ->when($isTeacher, fn ($query) => $query->where('teacher_id', $teacher->id))
            ->orderBy('starts_at')
            ->get();

        $sessions = $school->attendanceSessions()
            ->with(['classroom', 'schedule.subject', 'teacher.user', 'subject'])
            ->where('type', 'schedule')
            ->when($isTeacher, fn ($query) => $query->where('teacher_id', $teacher->id))
            ->latest('attendance_date')
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('attendances.schedule', [
            'school' => $school,
            'schedules' => $schedules,
            'sessions' => $sessions,
            'attendanceDate' => $attendanceDate,
            'selectedDay' => $selectedDay,
            'days' => $this->days(),
        ]);
    }

    public function openSchedule(Request $request): RedirectResponse
    {
        $school = $this->activeSchool($request);

        if (! $school) {
            return redirect()->route('dashboard')->withErrors('Akun Anda belum terhubung ke sekolah aktif.');
        }

        $teacher = $this->activeTeacher($request, $school->id);
        $isTeacher = $this->effectiveRole($request) === 'teacher';

        $validated = $request->validate([
            'attendance_date' => ['required', 'date'],
            'schedule_id' => ['required', Rule::exists('schedules', 'id')->where('school_id', $school->id)],
        ]);

        $schedule = $school->schedules()
            ->with(['classroom', 'teacher', 'subject'])
            ->where('is_active', true)
            ->whereKey($validated['schedule_id'])
            ->firstOrFail();

        if ($isTeacher && (! $teacher || $schedule->teacher_id !== $teacher->id)) {
            return back()
                ->withInput()
                ->withErrors('Guru hanya dapat membuka presensi per jadwal untuk jadwal mengajarnya sendiri.');
        }

        $selectedDay = Carbon::parse($validated['attendance_date'])->isoWeekday();

        if ((int) $schedule->day_of_week !== (int) $selectedDay) {
            return back()
                ->withInput()
                ->withErrors('Jadwal yang dipilih bukan jadwal hari '.$this->days()[$selectedDay].'.');
        }

        $session = DB::transaction(function () use ($school, $schedule, $validated) {
            $session = AttendanceSession::firstOrCreate([
                'school_id' => $school->id,
                'classroom_id' => $schedule->classroom_id,
                'attendance_date' => $validated['attendance_date'],
                'type' => 'schedule',
                'schedule_id' => $schedule->id,
            ], [
                'teacher_id' => $schedule->teacher_id,
                'subject_id' => $schedule->subject_id,
                'starts_at' => $schedule->starts_at,
                'ends_at' => $schedule->ends_at,
                'status' => 'draft',
            ]);

            $this->syncAttendanceRecords($session, $schedule->classroom);

            return $session;
        });

        return redirect()->route('attendances.schedule.edit', $session);
    }

    public function editSchedule(Request $request, AttendanceSession $attendanceSession): View|RedirectResponse
    {
        $school = $this->activeSchool($request);

        if (! $school || $attendanceSession->school_id !== $school->id || $attendanceSession->type !== 'schedule') {
            abort(403);
        }

        if (! $this->canManageScheduleSession($request, $attendanceSession)) {
            abort(403);
        }

        $attendanceSession->load(['classroom', 'schedule.physicalRoom', 'teacher.user', 'subject', 'records.student.user']);

        return view('attendances.schedule-edit', [
            'school' => $school,
            'session' => $attendanceSession,
            'recordStatuses' => self::RECORD_STATUSES,
            'days' => $this->days(),
        ]);
    }

    public function updateSchedule(Request $request, AttendanceSession $attendanceSession): RedirectResponse
    {
        $school = $this->activeSchool($request);

        if (! $school || $attendanceSession->school_id !== $school->id || $attendanceSession->type !== 'schedule') {
            abort(403);
        }

        if (! $this->canManageScheduleSession($request, $attendanceSession)) {
            abort(403);
        }

        if ($attendanceSession->status !== 'draft') {
            return back()->withErrors('Presensi yang sudah disubmit atau dikunci tidak dapat diperbarui.');
        }

        return $this->updateAttendanceSession(
            $request,
            $attendanceSession,
            'attendances.schedule.edit',
            'Presensi per jadwal berhasil disubmit.',
            'Draft presensi per jadwal berhasil disimpan.'
        );
    }

    public function editDaily(Request $request, AttendanceSession $attendanceSession): View|RedirectResponse
    {
        $school = $this->activeSchool($request);

        if (! $school || $attendanceSession->school_id !== $school->id || $attendanceSession->type !== 'daily') {
            abort(403);
        }

        if (! $this->canManageDailySession($request, $attendanceSession)) {
            abort(403);
        }

        $attendanceSession->load(['classroom', 'teacher.user', 'records.student.user']);

        return view('attendances.daily-edit', [
            'school' => $school,
            'session' => $attendanceSession,
            'recordStatuses' => self::RECORD_STATUSES,
        ]);
    }

    public function scanDaily(Request $request, AttendanceSession $attendanceSession): JsonResponse
    {
        if (! $this->canManageDailySession($request, $attendanceSession)) {
            abort(403);
        }

        if ($attendanceSession->status !== 'draft') {
            return response()->json([
                'message' => 'Presensi yang sudah disubmit atau dikunci tidak dapat discan.',
            ], 422);
        }

        return $this->scanAttendanceSession($request, $attendanceSession, 'daily');
    }

    public function scanSchedule(Request $request, AttendanceSession $attendanceSession): JsonResponse
    {
        if (! $this->canManageScheduleSession($request, $attendanceSession)) {
            abort(403);
        }

        if ($attendanceSession->status !== 'draft') {
            return response()->json([
                'message' => 'Presensi yang sudah disubmit atau dikunci tidak dapat discan.',
            ], 422);
        }

        return $this->scanAttendanceSession($request, $attendanceSession, 'schedule');
    }

    private function scanAttendanceSession(Request $request, AttendanceSession $attendanceSession, string $type): JsonResponse
    {
        $school = $this->activeSchool($request);

        if (! $school || $attendanceSession->school_id !== $school->id || $attendanceSession->type !== $type) {
            abort(403);
        }

        if ($attendanceSession->status !== 'draft') {
            return response()->json([
                'message' => 'Presensi yang sudah disubmit atau dikunci tidak dapat diperbarui.',
            ], 422);
        }

        $validated = $request->validate([
            'student_id' => ['required', 'string', 'max:255'],
        ]);

        $studentId = $this->extractUuid($validated['student_id']);

        if (! $studentId) {
            return response()->json([
                'message' => 'QR Code tidak valid. Pastikan QR berasal dari nametag murid.',
            ], 422);
        }

        $student = Student::with('user')
            ->where('school_id', $school->id)
            ->whereKey($studentId)
            ->first();

        if (! $student) {
            return response()->json([
                'message' => 'QR Code terbaca, tetapi data tersebut bukan nametag murid pada sekolah ini.',
            ], 404);
        }

        $attendanceSession->loadMissing('classroom');

        $isClassroomMember = $attendanceSession->classroom
            ? $attendanceSession->classroom->students()
                ->whereKey($student->id)
                ->wherePivot('status', 'active')
                ->where('students.is_active', true)
                ->exists()
            : false;

        if (! $isClassroomMember) {
            return response()->json([
                'message' => ($student->user?->name ?? 'Murid ini').' terdaftar sebagai murid, tetapi bukan anggota rombel '.$attendanceSession->classroom?->name.'.',
                'student' => [
                    'id' => $student->id,
                    'name' => $student->user?->name,
                    'nis' => $student->nis,
                    'nisn' => $student->nisn,
                ],
            ], 422);
        }

        $record = $attendanceSession->records()->firstOrCreate([
            'student_id' => $student->id,
        ], [
            'status' => null,
            'checked_at' => null,
        ]);

        $wasPresent = $record->status === 'present' && $record->checked_at;

        $record->update([
            'status' => 'present',
            'checked_at' => now(),
        ]);

        return response()->json([
            'message' => $wasPresent
                ? ($student->user?->name ?? 'Murid').' sudah tercatat hadir. Waktu scan diperbarui.'
                : ($student->user?->name ?? 'Murid').' berhasil diabsen hadir.',
            'record' => [
                'id' => $record->id,
                'status' => $record->status,
                'status_label' => self::RECORD_STATUSES[$record->status] ?? $record->status,
                'checked_at' => $record->checked_at?->format('d M Y H:i:s'),
            ],
            'student' => [
                'id' => $student->id,
                'name' => $student->user?->name,
                'nis' => $student->nis,
                'nisn' => $student->nisn,
            ],
        ]);
    }

    public function updateDaily(Request $request, AttendanceSession $attendanceSession): RedirectResponse
    {
        $school = $this->activeSchool($request);

        if (! $school || $attendanceSession->school_id !== $school->id || $attendanceSession->type !== 'daily') {
            abort(403);
        }

        if (! $this->canManageDailySession($request, $attendanceSession)) {
            abort(403);
        }

        if ($attendanceSession->status !== 'draft') {
            return back()->withErrors('Presensi yang sudah disubmit atau dikunci tidak dapat diperbarui.');
        }

        return $this->updateAttendanceSession(
            $request,
            $attendanceSession,
            'attendances.daily.edit',
            'Presensi per kelas berhasil disubmit.',
            'Draft presensi per kelas berhasil disimpan.'
        );
    }

    private function updateAttendanceSession(
        Request $request,
        AttendanceSession $attendanceSession,
        string $redirectRoute,
        string $submittedMessage,
        string $draftMessage
    ): RedirectResponse {
        if ($attendanceSession->status !== 'draft') {
            return back()->withErrors('Presensi yang sudah disubmit atau dikunci tidak dapat diperbarui.');
        }

        $validated = $request->validate([
            'records' => ['required', 'array'],
            'records.*.status' => ['nullable', Rule::in(array_keys(self::RECORD_STATUSES))],
            'records.*.notes' => ['nullable', 'string', 'max:1000'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'action' => ['required', Rule::in(['draft', 'submit'])],
        ]);

        if ($validated['action'] === 'submit') {
            $emptyStatusCount = collect($validated['records'])
                ->filter(fn ($recordData) => empty($recordData['status']))
                ->count();

            if ($emptyStatusCount > 0) {
                return back()
                    ->withInput()
                    ->withErrors('Masih ada '.$emptyStatusCount.' murid yang belum dipilih status presensinya.');
            }
        }

        DB::transaction(function () use ($attendanceSession, $validated) {
            foreach ($validated['records'] as $recordId => $recordData) {
                $record = $attendanceSession->records()->whereKey($recordId)->first();

                if (! $record) {
                    continue;
                }

                $record->update([
                    'status' => $recordData['status'] ?? null,
                    'notes' => $recordData['notes'] ?? null,
                    'checked_at' => empty($recordData['status']) ? null : now(),
                ]);
            }

            $attendanceSession->update([
                'status' => $validated['action'] === 'submit' ? 'submitted' : 'draft',
                'notes' => $validated['notes'] ?? null,
            ]);
        });

        return redirect()
            ->route($redirectRoute, $attendanceSession)
            ->with('success', $validated['action'] === 'submit' ? $submittedMessage : $draftMessage);
    }

    private function syncDailyRecords(AttendanceSession $session, Classroom $classroom): void
    {
        $this->syncAttendanceRecords($session, $classroom);
    }

    private function syncAttendanceRecords(AttendanceSession $session, Classroom $classroom): void
    {
        $students = $classroom->students()
            ->wherePivot('status', 'active')
            ->where('students.is_active', true)
            ->get();

        foreach ($students as $student) {
            $session->records()->firstOrCreate([
                'student_id' => $student->id,
            ], [
                'status' => null,
                'checked_at' => null,
            ]);
        }
    }

    private function reportFilters(Request $request): array
    {
        return [
            'date_from' => $request->input('date_from', now()->startOfMonth()->format('Y-m-d')),
            'date_to' => $request->input('date_to', now()->format('Y-m-d')),
            'type' => $request->input('type', ''),
            'classroom_id' => $request->input('classroom_id', ''),
            'status' => $request->input('status', ''),
        ];
    }

    private function dailyReportFilters(Request $request): array
    {
        return [
            'date_from' => $request->input('date_from', now()->startOfMonth()->format('Y-m-d')),
            'date_to' => $request->input('date_to', now()->format('Y-m-d')),
            'classroom_id' => $request->input('classroom_id', ''),
            'status' => $request->input('status', ''),
        ];
    }

    private function periodReportFilters(Request $request): array
    {
        $validated = $request->validate([
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
            'classroom_id' => ['nullable', 'string'],
        ]);

        return [
            'date_from' => $validated['date_from'] ?? now()->startOfMonth()->format('Y-m-d'),
            'date_to' => $validated['date_to'] ?? now()->format('Y-m-d'),
            'classroom_id' => $validated['classroom_id'] ?? '',
        ];
    }

    private function buildDailyPeriodReport($school, array $filters): array
    {
        $periodStart = Carbon::parse($filters['date_from'])->startOfDay();
        $periodEnd = Carbon::parse($filters['date_to'])->startOfDay();
        $effectiveDates = collect();

        for ($date = $periodStart->copy(); $date->lte($periodEnd); $date->addDay()) {
            if ($this->attendanceDayContext($school, $date)['is_attendance_day']) {
                $effectiveDates->push($date->toDateString());
            }
        }

        $classrooms = $school->classrooms()
            ->with([
                'academicYear',
                'semester',
                'homeroomTeacher.user',
                'students' => fn ($query) => $query
                    ->wherePivot('status', 'active')
                    ->where('students.is_active', true)
                    ->with('user')
                    ->orderBy('students.nis'),
            ])
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $selectedClassroom = $filters['classroom_id']
            ? $classrooms->firstWhere('id', $filters['classroom_id'])
            : null;

        if ($filters['classroom_id'] && ! $selectedClassroom) {
            return [
                'classrooms' => $classrooms,
                'selected_classroom' => null,
                'effective_dates' => $effectiveDates,
                'summary_rows' => collect(),
                'totals' => $this->emptyDailyPeriodCounts(),
            ];
        }

        $reportClassrooms = $selectedClassroom ? collect([$selectedClassroom]) : $classrooms;
        $studentIds = $reportClassrooms
            ->flatMap(fn ($classroom) => $classroom->students->pluck('id'))
            ->unique()
            ->values();

        $attendanceRows = $studentIds->isEmpty() || $effectiveDates->isEmpty()
            ? collect()
            : StudentDailyAttendance::query()
                ->where('school_id', $school->id)
                ->whereIn('student_id', $studentIds)
                ->whereIn('attendance_date', $effectiveDates->all())
                ->get()
                ->groupBy('student_id');

        $effectiveDayCount = $effectiveDates->count();
        $summaryRows = $reportClassrooms->map(function ($classroom) use ($attendanceRows, $effectiveDayCount) {
            $studentRows = $classroom->students
                ->sortBy(fn ($student) => $student->user?->name)
                ->values()
                ->map(function ($student) use ($attendanceRows, $effectiveDayCount) {
                    $counts = $this->dailyPeriodCounts($attendanceRows->get($student->id, collect()), $effectiveDayCount);

                    return [
                        'student' => $student,
                        ...$counts,
                    ];
                });

            $counts = $studentRows->reduce(function ($carry, $row) {
                foreach (['target', 'present', 'sick', 'permit', 'absent', 'unprocessed', 'late', 'explained'] as $key) {
                    $carry[$key] += $row[$key];
                }

                return $carry;
            }, $this->emptyDailyPeriodCounts());

            $counts['student_count'] = $classroom->students->count();
            $counts['effective_days'] = $effectiveDayCount;
            $counts['present_percent'] = $this->dailyPeriodPercent($counts['present'], $counts['target']);
            $counts['sick_percent'] = $this->dailyPeriodPercent($counts['sick'], $counts['target']);
            $counts['permit_percent'] = $this->dailyPeriodPercent($counts['permit'], $counts['target']);
            $counts['absent_percent'] = $this->dailyPeriodPercent($counts['absent'], $counts['target']);
            $counts['explained_percent'] = $this->dailyPeriodPercent($counts['explained'], $counts['target']);
            $counts['unprocessed_percent'] = $this->dailyPeriodPercent($counts['unprocessed'], $counts['target']);

            return [
                'classroom' => $classroom,
                'students' => $studentRows,
                ...$counts,
            ];
        });

        $totals = $summaryRows->reduce(function ($carry, $row) {
            foreach (['student_count', 'target', 'present', 'sick', 'permit', 'absent', 'unprocessed', 'late', 'explained'] as $key) {
                $carry[$key] += $row[$key];
            }

            return $carry;
        }, $this->emptyDailyPeriodCounts());
        $totals['effective_days'] = $effectiveDayCount;
        $totals['present_percent'] = $this->dailyPeriodPercent($totals['present'], $totals['target']);
        $totals['sick_percent'] = $this->dailyPeriodPercent($totals['sick'], $totals['target']);
        $totals['permit_percent'] = $this->dailyPeriodPercent($totals['permit'], $totals['target']);
        $totals['absent_percent'] = $this->dailyPeriodPercent($totals['absent'], $totals['target']);
        $totals['explained_percent'] = $this->dailyPeriodPercent($totals['explained'], $totals['target']);
        $totals['unprocessed_percent'] = $this->dailyPeriodPercent($totals['unprocessed'], $totals['target']);

        return [
            'classrooms' => $classrooms,
            'selected_classroom' => $selectedClassroom,
            'effective_dates' => $effectiveDates,
            'summary_rows' => $summaryRows,
            'totals' => $totals,
        ];
    }

    private function dailyPeriodCounts($rows, int $effectiveDayCount): array
    {
        $dates = [
            'present' => [],
            'sick' => [],
            'permit' => [],
            'absent' => [],
            'late' => [],
        ];

        foreach ($rows as $row) {
            $date = $row->attendance_date?->toDateString();

            if (! $date) {
                continue;
            }

            if ($row->check_in_at) {
                $dates['present'][$date] = true;

                if ($row->check_in_status === 'late') {
                    $dates['late'][$date] = true;
                }

                continue;
            }

            if (array_key_exists($row->status, self::DAILY_MANUAL_STATUSES)) {
                $dates[$row->status][$date] = true;
            }
        }

        $counts = $this->emptyDailyPeriodCounts();
        $counts['target'] = $effectiveDayCount;
        $counts['present'] = count($dates['present']);
        $counts['sick'] = count($dates['sick']);
        $counts['permit'] = count($dates['permit']);
        $counts['absent'] = count($dates['absent']);
        $counts['late'] = count($dates['late']);
        $counts['explained'] = $counts['sick'] + $counts['permit'] + $counts['absent'];
        $counts['unprocessed'] = max($effectiveDayCount - $counts['present'] - $counts['explained'], 0);
        $counts['present_percent'] = $this->dailyPeriodPercent($counts['present'], $counts['target']);
        $counts['sick_percent'] = $this->dailyPeriodPercent($counts['sick'], $counts['target']);
        $counts['permit_percent'] = $this->dailyPeriodPercent($counts['permit'], $counts['target']);
        $counts['absent_percent'] = $this->dailyPeriodPercent($counts['absent'], $counts['target']);
        $counts['explained_percent'] = $this->dailyPeriodPercent($counts['explained'], $counts['target']);
        $counts['unprocessed_percent'] = $this->dailyPeriodPercent($counts['unprocessed'], $counts['target']);

        return $counts;
    }

    private function emptyDailyPeriodCounts(): array
    {
        return [
            'student_count' => 0,
            'effective_days' => 0,
            'target' => 0,
            'present' => 0,
            'sick' => 0,
            'permit' => 0,
            'absent' => 0,
            'unprocessed' => 0,
            'late' => 0,
            'explained' => 0,
            'present_percent' => 0,
            'sick_percent' => 0,
            'permit_percent' => 0,
            'absent_percent' => 0,
            'explained_percent' => 0,
            'unprocessed_percent' => 0,
        ];
    }

    private function dailyPeriodPercent(int $value, int $target): float
    {
        return $target > 0 ? round(($value / $target) * 100, 1) : 0;
    }

    private function dailyReportRecordsQuery($school, array $filters, ?Teacher $teacher = null, bool $isTeacher = false)
    {
        return AttendanceRecord::query()
            ->with(['student.user', 'session.classroom', 'session.teacher.user'])
            ->whereHas('session', function ($query) use ($school, $filters, $teacher, $isTeacher) {
                $query->where('school_id', $school->id)
                    ->where('type', 'daily')
                    ->when($filters['date_from'], fn ($inner) => $inner->whereDate('attendance_date', '>=', $filters['date_from']))
                    ->when($filters['date_to'], fn ($inner) => $inner->whereDate('attendance_date', '<=', $filters['date_to']))
                    ->when($filters['classroom_id'], fn ($inner) => $inner->where('classroom_id', $filters['classroom_id']))
                    ->when($isTeacher, fn ($inner) => $teacher
                        ? $inner->where('teacher_id', $teacher->id)
                        : $inner->whereRaw('1 = 0'));
            })
            ->when($filters['status'] === 'unfilled', fn ($query) => $query->whereNull('attendance_records.status'))
            ->when($filters['status'] && $filters['status'] !== 'unfilled', fn ($query) => $query->where('attendance_records.status', $filters['status']))
            ->join('attendance_sessions', 'attendance_records.attendance_session_id', '=', 'attendance_sessions.id')
            ->join('students', 'attendance_records.student_id', '=', 'students.id')
            ->join('users', 'students.user_id', '=', 'users.id')
            ->orderByDesc('attendance_sessions.attendance_date')
            ->orderBy('attendance_sessions.classroom_id')
            ->orderBy('users.name')
            ->select('attendance_records.*');
    }

    private function scheduleReportFilters(Request $request): array
    {
        return [
            'date_from' => $request->input('date_from', now()->startOfMonth()->format('Y-m-d')),
            'date_to' => $request->input('date_to', now()->format('Y-m-d')),
            'classroom_id' => $request->input('classroom_id', ''),
            'subject_id' => $request->input('subject_id', ''),
            'status' => $request->input('status', ''),
        ];
    }

    private function scheduleReportRecordsQuery($school, array $filters, ?Teacher $teacher = null, bool $isTeacher = false)
    {
        return AttendanceRecord::query()
            ->with(['student.user', 'session.classroom.academicYear', 'session.classroom.semester', 'session.subject', 'session.teacher.user', 'session.schedule.subject'])
            ->whereHas('session', function ($query) use ($school, $filters, $teacher, $isTeacher) {
                $query->where('school_id', $school->id)
                    ->where('type', 'schedule')
                    ->when($filters['date_from'], fn ($inner) => $inner->whereDate('attendance_date', '>=', $filters['date_from']))
                    ->when($filters['date_to'], fn ($inner) => $inner->whereDate('attendance_date', '<=', $filters['date_to']))
                    ->when($filters['classroom_id'], fn ($inner) => $inner->where('classroom_id', $filters['classroom_id']))
                    ->when($filters['subject_id'], fn ($inner) => $inner->where('subject_id', $filters['subject_id']))
                    ->when($isTeacher, fn ($inner) => $teacher
                        ? $inner->where('teacher_id', $teacher->id)
                        : $inner->whereRaw('1 = 0'));
            })
            ->when($filters['status'] === 'unfilled', fn ($query) => $query->whereNull('attendance_records.status'))
            ->when($filters['status'] && $filters['status'] !== 'unfilled', fn ($query) => $query->where('attendance_records.status', $filters['status']))
            ->join('attendance_sessions', 'attendance_records.attendance_session_id', '=', 'attendance_sessions.id')
            ->join('students', 'attendance_records.student_id', '=', 'students.id')
            ->join('users', 'students.user_id', '=', 'users.id')
            ->orderByDesc('attendance_sessions.attendance_date')
            ->orderBy('attendance_sessions.classroom_id')
            ->orderBy('attendance_sessions.starts_at')
            ->orderBy('users.name')
            ->select('attendance_records.*');
    }

    private function reportRecordsQuery($school, array $filters)
    {
        return AttendanceRecord::query()
            ->with(['student.user', 'session.classroom', 'session.subject', 'session.teacher.user', 'session.schedule.subject'])
            ->whereHas('session', function ($query) use ($school, $filters) {
                $query->where('school_id', $school->id)
                    ->when($filters['date_from'], fn ($inner) => $inner->whereDate('attendance_date', '>=', $filters['date_from']))
                    ->when($filters['date_to'], fn ($inner) => $inner->whereDate('attendance_date', '<=', $filters['date_to']))
                    ->when($filters['type'], fn ($inner) => $inner->where('type', $filters['type']))
                    ->when($filters['classroom_id'], fn ($inner) => $inner->where('classroom_id', $filters['classroom_id']));
            })
            ->when($filters['status'] === 'unfilled', fn ($query) => $query->whereNull('status'))
            ->when($filters['status'] && $filters['status'] !== 'unfilled', fn ($query) => $query->where('status', $filters['status']))
            ->join('attendance_sessions', 'attendance_records.attendance_session_id', '=', 'attendance_sessions.id')
            ->orderByDesc('attendance_sessions.attendance_date')
            ->orderBy('attendance_sessions.type')
            ->orderBy('attendance_records.created_at')
            ->select('attendance_records.*');
    }

    private function dailyDashboardStudentQuery($school, ?string $classroomId)
    {
        return Student::query()
            ->where('students.school_id', $school->id)
            ->where('students.is_active', true)
            ->when($classroomId, fn ($query) => $query->whereHas('classrooms', function ($inner) use ($classroomId) {
                $inner->whereKey($classroomId)
                    ->where('classroom_student.status', 'active')
                    ->where('classrooms.is_active', true);
            }));
    }

    private function schoolAttendanceDays($school): array
    {
        $days = $school->school_attendance_days ?: [1, 2, 3, 4, 5, 6];

        return collect($days)
            ->map(fn ($day) => (int) $day)
            ->filter(fn ($day) => $day >= 1 && $day <= 7)
            ->unique()
            ->sort()
            ->values()
            ->all();
    }

    private function isSchoolAttendanceDay($school, Carbon $date): bool
    {
        return $this->attendanceDayContext($school, $date)['is_attendance_day'];
    }

    private function attendanceDayContext($school, Carbon $date): array
    {
        $date = $date->copy()->startOfDay();
        $events = AcademicCalendarEvent::query()
            ->where('school_id', $school->id)
            ->whereDate('starts_at', '<=', $date->toDateString())
            ->whereDate('ends_at', '>=', $date->toDateString())
            ->orderBy('starts_at')
            ->orderBy('title')
            ->get();

        $attendanceEvent = $events->firstWhere('attendance_effect', 'attendance_day');

        if ($attendanceEvent) {
            return [
                'is_attendance_day' => true,
                'reason' => 'calendar_attendance_day',
                'message' => 'Kalender akademik menetapkan tanggal ini tetap dihitung presensi.',
                'events' => $events,
                'semester' => $this->semesterForAttendanceDate($school, $date),
            ];
        }

        $nonAttendanceEvent = $events->firstWhere('attendance_effect', 'non_attendance_day');

        if ($nonAttendanceEvent) {
            return [
                'is_attendance_day' => false,
                'reason' => 'calendar_non_attendance_day',
                'message' => 'Kalender akademik menetapkan tanggal ini tidak dihitung presensi.',
                'events' => $events,
                'semester' => $this->semesterForAttendanceDate($school, $date),
            ];
        }

        $activeSemesterExists = $school->semesters()
            ->where('is_active', true)
            ->exists();
        $semester = $this->semesterForAttendanceDate($school, $date);

        if ($activeSemesterExists && ! $semester) {
            return [
                'is_attendance_day' => false,
                'reason' => 'outside_active_semester',
                'message' => 'Tanggal ini berada di luar rentang semester aktif.',
                'events' => $events,
                'semester' => null,
            ];
        }

        $isSchoolDay = in_array($date->isoWeekday(), $this->schoolAttendanceDays($school), true);

        return [
            'is_attendance_day' => $isSchoolDay,
            'reason' => $isSchoolDay ? 'school_day' : 'weekly_non_attendance_day',
            'message' => $isSchoolDay
                ? 'Tanggal ini mengikuti hari sekolah aktif.'
                : 'Tanggal ini tidak termasuk dalam hari sekolah aktif.',
            'events' => $events,
            'semester' => $semester,
        ];
    }

    private function semesterForAttendanceDate($school, Carbon $date)
    {
        return $school->semesters()
            ->where('is_active', true)
            ->whereDate('starts_at', '<=', $date->toDateString())
            ->whereDate('ends_at', '>=', $date->toDateString())
            ->orderByDesc('starts_at')
            ->first();
    }

    private function schoolAttendanceDayLabels($school): array
    {
        $labels = [
            1 => 'Senin',
            2 => 'Selasa',
            3 => 'Rabu',
            4 => 'Kamis',
            5 => 'Jumat',
            6 => 'Sabtu',
            7 => 'Minggu',
        ];

        return collect($this->schoolAttendanceDays($school))
            ->map(fn ($day) => $labels[$day])
            ->all();
    }

    private function dailyCheckInEvaluation($school, Carbon $scanAt): array
    {
        $lateLimit = $this->dailyTimeForDate(
            $scanAt->toDateString(),
            $school->daily_check_in_time,
            '07:00:00'
        )->addMinutes((int) ($school->daily_late_tolerance_minutes ?? 10));

        if ($scanAt->gt($lateLimit)) {
            return [
                'status' => 'late',
                'late_minutes' => $lateLimit->diffInMinutes($scanAt),
            ];
        }

        return [
            'status' => 'on_time',
            'late_minutes' => 0,
        ];
    }

    private function dailyCheckOutEvaluation($school, Carbon $scanAt): array
    {
        $earlyLimit = $this->dailyTimeForDate(
            $scanAt->toDateString(),
            $school->daily_check_out_time,
            '14:00:00'
        )->subMinutes((int) ($school->daily_early_leave_tolerance_minutes ?? 0));

        if ($scanAt->lt($earlyLimit)) {
            return [
                'status' => 'early',
                'early_leave_minutes' => $scanAt->diffInMinutes($earlyLimit),
            ];
        }

        return [
            'status' => 'normal',
            'early_leave_minutes' => 0,
        ];
    }

    private function dailyTimeForDate(string $date, ?string $time, string $fallback): Carbon
    {
        return Carbon::parse($date.' '.($time ?: $fallback));
    }

    private function dailyCheckInStatusLabels(): array
    {
        return [
            'on_time' => 'Tepat waktu',
            'late' => 'Terlambat',
        ];
    }

    private function dailyCheckOutStatusLabels(): array
    {
        return [
            'normal' => 'Normal',
            'early' => 'Pulang cepat',
        ];
    }

    private function extractUuid(string $value): ?string
    {
        $trimmed = trim($value);

        if (preg_match('/[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{12}/', $trimmed, $matches)) {
            return strtolower($matches[0]);
        }

        return null;
    }

    private function canManageDailySession(Request $request, AttendanceSession $session): bool
    {
        if ($this->effectiveRole($request) !== 'teacher') {
            return true;
        }

        $teacher = $this->activeTeacher($request, $session->school_id);

        return $teacher && $session->classroom_id && Classroom::query()
            ->whereKey($session->classroom_id)
            ->where('homeroom_teacher_id', $teacher->id)
            ->exists();
    }

    private function canManageScheduleSession(Request $request, AttendanceSession $session): bool
    {
        if ($this->effectiveRole($request) !== 'teacher') {
            return true;
        }

        $teacher = $this->activeTeacher($request, $session->school_id);

        return $teacher && $session->teacher_id === $teacher->id;
    }

    private function activeTeacher(Request $request, string $schoolId): ?Teacher
    {
        $user = EffectiveAccess::user($request);

        if (! $user) {
            return null;
        }

        return Teacher::query()
            ->where('school_id', $schoolId)
            ->where('user_id', $user->id)
            ->where('is_active', true)
            ->first();
    }

    private function activeSchool(Request $request)
    {
        return EffectiveAccess::school($request);
    }

    private function effectiveRole(Request $request): ?string
    {
        return EffectiveAccess::role($request);
    }

    private function days(): array
    {
        return [
            1 => 'Senin',
            2 => 'Selasa',
            3 => 'Rabu',
            4 => 'Kamis',
            5 => 'Jumat',
            6 => 'Sabtu',
            7 => 'Minggu',
        ];
    }
}
