<?php

namespace App\Models;

use App\Models\Concerns\HasUuidPrimaryKey;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use App\Models\Role;

class School extends Model
{
    use HasFactory, HasUuidPrimaryKey;

    protected $fillable = [
        'name',
        'npsn',
        'level',
        'address',
        'phone',
        'email',
        'logo_path',
        'nametag_background_path',
        'status',
        'approved_at',
        'approved_by',
        'onboarding_completed_at',
        'daily_check_in_time',
        'daily_late_tolerance_minutes',
        'daily_check_out_time',
        'daily_early_leave_tolerance_minutes',
        'daily_min_checkout_minutes',
        'school_attendance_days',
        'teacher_check_in_time',
        'teacher_late_tolerance_minutes',
        'teacher_check_out_time',
        'teacher_early_leave_tolerance_minutes',
        'teacher_attendance_latitude',
        'teacher_attendance_longitude',
        'teacher_attendance_radius_meters',
        'teacher_attendance_max_accuracy_meters',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
        'onboarding_completed_at' => 'datetime',
        'daily_late_tolerance_minutes' => 'integer',
        'daily_early_leave_tolerance_minutes' => 'integer',
        'daily_min_checkout_minutes' => 'integer',
        'school_attendance_days' => 'array',
        'teacher_late_tolerance_minutes' => 'integer',
        'teacher_early_leave_tolerance_minutes' => 'integer',
        'teacher_attendance_latitude' => 'decimal:7',
        'teacher_attendance_longitude' => 'decimal:7',
        'teacher_attendance_radius_meters' => 'integer',
        'teacher_attendance_max_accuracy_meters' => 'integer',
    ];

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
            ->using(SchoolUser::class)
            ->withPivot(['role_id', 'status'])
            ->withTimestamps();
    }

    public function schoolAdmins(): BelongsToMany
    {
        $schoolAdminRoleId = Role::where('name', 'school_admin')->value('id');

        return $this->users()->wherePivot('role_id', $schoolAdminRoleId);
    }

    public function domains(): HasMany
    {
        return $this->hasMany(SchoolDomain::class);
    }

    public function primaryDomain(): HasOne
    {
        return $this->hasOne(SchoolDomain::class)->where('is_primary', true);
    }

    public function academicYears(): HasMany
    {
        return $this->hasMany(AcademicYear::class);
    }

    public function semesters(): HasMany
    {
        return $this->hasMany(Semester::class);
    }

    public function academicCalendarEvents(): HasMany
    {
        return $this->hasMany(AcademicCalendarEvent::class);
    }

    public function modules(): BelongsToMany
    {
        return $this->belongsToMany(Module::class)
            ->withPivot(['is_enabled', 'enabled_at', 'enabled_by'])
            ->withTimestamps();
    }

    public function classes(): HasMany
    {
        return $this->hasMany(SchoolClass::class);
    }

    public function subjects(): HasMany
    {
        return $this->hasMany(Subject::class);
    }

    public function teachers(): HasMany
    {
        return $this->hasMany(Teacher::class);
    }

    public function teacherDailyAttendances(): HasMany
    {
        return $this->hasMany(TeacherDailyAttendance::class);
    }

    public function students(): HasMany
    {
        return $this->hasMany(Student::class);
    }

    public function classrooms(): HasMany
    {
        return $this->hasMany(Classroom::class);
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(Schedule::class);
    }

    public function rooms(): HasMany
    {
        return $this->hasMany(Room::class);
    }

    public function attendanceSessions(): HasMany
    {
        return $this->hasMany(AttendanceSession::class);
    }

    public function studentDailyAttendances(): HasMany
    {
        return $this->hasMany(StudentDailyAttendance::class);
    }

    public function exams(): HasMany
    {
        return $this->hasMany(Exam::class);
    }
}
