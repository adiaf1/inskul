<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('username')->nullable()->after('name');
        });

        $used = [];

        DB::table('users')
            ->select(['id', 'name', 'email'])
            ->orderBy('created_at')
            ->get()
            ->each(function ($user) use (&$used) {
                $source = $user->email ? Str::before($user->email, '@') : $user->name;
                $base = Str::of($source ?: 'user')
                    ->lower()
                    ->replaceMatches('/[^a-z0-9._-]+/', '.')
                    ->trim('.-_')
                    ->limit(40, '')
                    ->value() ?: 'user';

                $username = $base;
                $counter = 2;

                while (isset($used[$username])) {
                    $username = $base.$counter;
                    $counter++;
                }

                $used[$username] = true;

                DB::table('users')
                    ->where('id', $user->id)
                    ->update(['username' => $username]);
            });

        Schema::table('users', function (Blueprint $table) {
            $table->unique('username');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['username']);
            $table->dropColumn('username');
        });
    }
};
