<?php

namespace App\Models;

use App\Models\Concerns\HasUuidPrimaryKey;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TeacherDailyAttendance extends Model
{
    use HasFactory, HasUuidPrimaryKey;

    protected $fillable = [
        'school_id',
        'teacher_id',
        'attendance_date',
        'check_in_at',
        'check_in_photo_path',
        'check_in_latitude',
        'check_in_longitude',
        'check_in_accuracy',
        'check_in_distance_meters',
        'check_in_status',
        'check_out_at',
        'check_out_photo_path',
        'check_out_latitude',
        'check_out_longitude',
        'check_out_accuracy',
        'check_out_distance_meters',
        'check_out_status',
        'notes',
    ];

    protected $casts = [
        'attendance_date' => 'date',
        'check_in_at' => 'datetime',
        'check_out_at' => 'datetime',
        'check_in_latitude' => 'decimal:7',
        'check_in_longitude' => 'decimal:7',
        'check_out_latitude' => 'decimal:7',
        'check_out_longitude' => 'decimal:7',
    ];

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }
}
