<?php

namespace App\Models;

use App\Models\Concerns\HasUuidPrimaryKey;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentDailyAttendance extends Model
{
    use HasFactory, HasUuidPrimaryKey;

    protected $fillable = [
        'school_id',
        'student_id',
        'attendance_date',
        'check_in_at',
        'check_in_by',
        'check_in_status',
        'late_minutes',
        'check_out_at',
        'check_out_by',
        'check_out_status',
        'early_leave_minutes',
        'status',
        'notes',
    ];

    protected $casts = [
        'attendance_date' => 'date',
        'check_in_at' => 'datetime',
        'check_out_at' => 'datetime',
        'late_minutes' => 'integer',
        'early_leave_minutes' => 'integer',
    ];

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function checkInBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'check_in_by');
    }

    public function checkOutBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'check_out_by');
    }
}
