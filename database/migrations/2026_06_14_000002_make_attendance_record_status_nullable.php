<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE attendance_records MODIFY status VARCHAR(255) NULL DEFAULT NULL');
        DB::table('attendance_records')
            ->where('status', 'absent')
            ->whereNull('checked_at')
            ->update(['status' => null]);
    }

    public function down(): void
    {
        DB::table('attendance_records')
            ->whereNull('status')
            ->update(['status' => 'absent']);
        DB::statement("ALTER TABLE attendance_records MODIFY status VARCHAR(255) NOT NULL DEFAULT 'present'");
    }
};
