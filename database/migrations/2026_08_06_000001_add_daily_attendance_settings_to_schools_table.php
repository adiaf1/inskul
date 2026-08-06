<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('schools', function (Blueprint $table) {
            $table->time('daily_check_in_time')->default('07:00:00')->after('onboarding_completed_at');
            $table->unsignedSmallInteger('daily_late_tolerance_minutes')->default(10)->after('daily_check_in_time');
            $table->time('daily_check_out_time')->default('14:00:00')->after('daily_late_tolerance_minutes');
            $table->unsignedSmallInteger('daily_early_leave_tolerance_minutes')->default(0)->after('daily_check_out_time');
            $table->unsignedSmallInteger('daily_min_checkout_minutes')->default(60)->after('daily_early_leave_tolerance_minutes');
        });
    }

    public function down(): void
    {
        Schema::table('schools', function (Blueprint $table) {
            $table->dropColumn([
                'daily_check_in_time',
                'daily_late_tolerance_minutes',
                'daily_check_out_time',
                'daily_early_leave_tolerance_minutes',
                'daily_min_checkout_minutes',
            ]);
        });
    }
};
