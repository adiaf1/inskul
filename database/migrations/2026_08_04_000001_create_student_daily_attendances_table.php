<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_daily_attendances', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('school_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('student_id')->constrained()->cascadeOnDelete();
            $table->date('attendance_date');
            $table->timestamp('check_in_at')->nullable();
            $table->foreignUuid('check_in_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('check_out_at')->nullable();
            $table->foreignUuid('check_out_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status')->default('open');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['school_id', 'student_id', 'attendance_date'], 'student_daily_att_unique');
            $table->index(['school_id', 'attendance_date'], 'student_daily_att_school_date_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_daily_attendances');
    }
};
