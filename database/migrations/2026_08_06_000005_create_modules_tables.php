<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('modules', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('code')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('module_school', function (Blueprint $table) {
            $table->foreignUuid('module_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('school_id')->constrained()->cascadeOnDelete();
            $table->boolean('is_enabled')->default(true);
            $table->timestamp('enabled_at')->nullable();
            $table->foreignUuid('enabled_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->primary(['module_id', 'school_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('module_school');
        Schema::dropIfExists('modules');
    }
};
