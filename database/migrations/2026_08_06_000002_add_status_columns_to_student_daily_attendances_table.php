<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('student_daily_attendances', function (Blueprint $table) {
            $table->string('check_in_status')->nullable()->after('check_in_by');
            $table->unsignedSmallInteger('late_minutes')->default(0)->after('check_in_status');
            $table->string('check_out_status')->nullable()->after('check_out_by');
            $table->unsignedSmallInteger('early_leave_minutes')->default(0)->after('check_out_status');
        });
    }

    public function down(): void
    {
        Schema::table('student_daily_attendances', function (Blueprint $table) {
            $table->dropColumn([
                'check_in_status',
                'late_minutes',
                'check_out_status',
                'early_leave_minutes',
            ]);
        });
    }
};
