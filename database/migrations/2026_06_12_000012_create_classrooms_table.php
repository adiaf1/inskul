<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('classrooms', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('school_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('academic_year_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('semester_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('school_class_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('homeroom_teacher_id')->nullable()->constrained('teachers')->nullOnDelete();
            $table->string('name');
            $table->unsignedInteger('capacity')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['school_id', 'academic_year_id', 'semester_id', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('classrooms');
    }
};
