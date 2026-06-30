<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exams', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('school_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('subject_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('classroom_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('teacher_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->unsignedSmallInteger('duration_minutes')->default(60);
            $table->string('status')->default('draft');
            $table->timestamps();

            $table->index(['school_id', 'subject_id', 'classroom_id'], 'exams_school_subject_classroom_idx');
            $table->index(['status', 'starts_at', 'ends_at'], 'exams_status_time_idx');
        });

        Schema::create('exam_questions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('exam_id')->constrained()->cascadeOnDelete();
            $table->text('question_text');
            $table->unsignedSmallInteger('points')->default(1);
            $table->unsignedSmallInteger('sort_order')->default(1);
            $table->timestamps();

            $table->index(['exam_id', 'sort_order'], 'exam_questions_exam_order_idx');
        });

        Schema::create('exam_options', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('exam_question_id')->constrained()->cascadeOnDelete();
            $table->char('label', 1);
            $table->text('option_text');
            $table->boolean('is_correct')->default(false);
            $table->timestamps();

            $table->unique(['exam_question_id', 'label'], 'exam_options_question_label_unique');
        });

        Schema::create('exam_attempts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('exam_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('student_id')->constrained()->cascadeOnDelete();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->unsignedInteger('score')->default(0);
            $table->unsignedInteger('max_score')->default(0);
            $table->string('status')->default('in_progress');
            $table->timestamps();

            $table->unique(['exam_id', 'student_id'], 'exam_attempts_exam_student_unique');
            $table->index(['student_id', 'status'], 'exam_attempts_student_status_idx');
        });

        Schema::create('exam_answers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('exam_attempt_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('exam_question_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('exam_option_id')->nullable()->constrained()->nullOnDelete();
            $table->boolean('is_correct')->default(false);
            $table->unsignedSmallInteger('points_awarded')->default(0);
            $table->timestamps();

            $table->unique(['exam_attempt_id', 'exam_question_id'], 'exam_answers_attempt_question_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_answers');
        Schema::dropIfExists('exam_attempts');
        Schema::dropIfExists('exam_options');
        Schema::dropIfExists('exam_questions');
        Schema::dropIfExists('exams');
    }
};
