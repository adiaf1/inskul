<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('teacher_daily_attendances', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('school_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('teacher_id')->constrained()->cascadeOnDelete();
            $table->date('attendance_date');
            $table->timestamp('check_in_at')->nullable();
            $table->string('check_in_photo_path')->nullable();
            $table->decimal('check_in_latitude', 10, 7)->nullable();
            $table->decimal('check_in_longitude', 10, 7)->nullable();
            $table->unsignedInteger('check_in_accuracy')->nullable();
            $table->unsignedInteger('check_in_distance_meters')->nullable();
            $table->string('check_in_status')->nullable();
            $table->timestamp('check_out_at')->nullable();
            $table->string('check_out_photo_path')->nullable();
            $table->decimal('check_out_latitude', 10, 7)->nullable();
            $table->decimal('check_out_longitude', 10, 7)->nullable();
            $table->unsignedInteger('check_out_accuracy')->nullable();
            $table->unsignedInteger('check_out_distance_meters')->nullable();
            $table->string('check_out_status')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['school_id', 'teacher_id', 'attendance_date'], 'teacher_daily_att_unique');
            $table->index(['school_id', 'attendance_date'], 'teacher_daily_att_school_date_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teacher_daily_attendances');
    }
};
