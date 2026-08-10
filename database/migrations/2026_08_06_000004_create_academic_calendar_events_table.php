<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('academic_calendar_events', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('school_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('academic_year_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignUuid('semester_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->date('starts_at');
            $table->date('ends_at');
            $table->string('type')->default('other');
            $table->string('attendance_effect')->default('inherit');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['school_id', 'starts_at', 'ends_at']);
            $table->index(['school_id', 'attendance_effect']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('academic_calendar_events');
    }
};
