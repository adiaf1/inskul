<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendance_sessions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('school_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('classroom_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('schedule_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignUuid('teacher_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignUuid('subject_id')->nullable()->constrained()->nullOnDelete();
            $table->date('attendance_date');
            $table->string('type')->default('daily');
            $table->time('starts_at')->nullable();
            $table->time('ends_at')->nullable();
            $table->string('status')->default('draft');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['school_id', 'classroom_id', 'attendance_date', 'type', 'schedule_id'], 'attendance_sessions_unique_scope');
            $table->index(['school_id', 'attendance_date', 'type'], 'attendance_sessions_school_date_type_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_sessions');
    }
};
