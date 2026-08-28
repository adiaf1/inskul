<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('schools', 'pwa_shortcut_enabled')) {
            Schema::table('schools', function (Blueprint $table) {
                $table->boolean('pwa_shortcut_enabled')->default(true)->after('onboarding_completed_at');
            });
        }

        if (Schema::hasColumn('schools', 'apk_download_enabled')) {
            DB::table('schools')->update([
                'pwa_shortcut_enabled' => DB::raw('apk_download_enabled'),
            ]);

            Schema::table('schools', function (Blueprint $table) {
                $table->dropColumn('apk_download_enabled');
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasColumn('schools', 'apk_download_enabled')) {
            Schema::table('schools', function (Blueprint $table) {
                $table->boolean('apk_download_enabled')->default(true)->after('onboarding_completed_at');
            });
        }

        if (Schema::hasColumn('schools', 'pwa_shortcut_enabled')) {
            DB::table('schools')->update([
                'apk_download_enabled' => DB::raw('pwa_shortcut_enabled'),
            ]);

            Schema::table('schools', function (Blueprint $table) {
                $table->dropColumn('pwa_shortcut_enabled');
            });
        }
    }
};
