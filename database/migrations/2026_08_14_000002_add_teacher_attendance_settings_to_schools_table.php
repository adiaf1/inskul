<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('schools', function (Blueprint $table) {
            $table->time('teacher_check_in_time')->default('07:00:00')->after('school_attendance_days');
            $table->unsignedSmallInteger('teacher_late_tolerance_minutes')->default(10)->after('teacher_check_in_time');
            $table->time('teacher_check_out_time')->default('14:00:00')->after('teacher_late_tolerance_minutes');
            $table->unsignedSmallInteger('teacher_early_leave_tolerance_minutes')->default(0)->after('teacher_check_out_time');
            $table->decimal('teacher_attendance_latitude', 10, 7)->nullable()->after('teacher_early_leave_tolerance_minutes');
            $table->decimal('teacher_attendance_longitude', 10, 7)->nullable()->after('teacher_attendance_latitude');
            $table->unsignedSmallInteger('teacher_attendance_radius_meters')->default(150)->after('teacher_attendance_longitude');
            $table->unsignedSmallInteger('teacher_attendance_max_accuracy_meters')->default(200)->after('teacher_attendance_radius_meters');
        });
    }

    public function down(): void
    {
        Schema::table('schools', function (Blueprint $table) {
            $table->dropColumn([
                'teacher_check_in_time',
                'teacher_late_tolerance_minutes',
                'teacher_check_out_time',
                'teacher_early_leave_tolerance_minutes',
                'teacher_attendance_latitude',
                'teacher_attendance_longitude',
                'teacher_attendance_radius_meters',
                'teacher_attendance_max_accuracy_meters',
            ]);
        });
    }
};
