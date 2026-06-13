<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendance_records', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('attendance_session_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('student_id')->constrained()->cascadeOnDelete();
            $table->string('status')->default('present');
            $table->timestamp('checked_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['attendance_session_id', 'student_id'], 'attendance_records_session_student_unique');
            $table->index(['student_id', 'status'], 'attendance_records_student_status_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_records');
    }
};
