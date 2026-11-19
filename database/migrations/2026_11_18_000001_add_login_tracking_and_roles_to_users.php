<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // NULL means "has never signed in" -> the first sign-in says "Welcome".
            $table->timestamp('last_login_at')->nullable()->after('password');
            // Lets the administrator switch an account off without deleting it.
            $table->boolean('is_active')->default(true)->after('last_login_at');
            $table->string('role', 20)->default('farmer')->change();
        });

        // The system now has two roles: admin and farmer.
        DB::table('users')->whereIn('role', ['member', 'supervisor'])->update(['role' => 'farmer']);
    }

    public function down(): void
    {
        DB::table('users')->where('role', 'farmer')->update(['role' => 'member']);

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['last_login_at', 'is_active']);
            $table->string('role', 20)->default('member')->change();
        });
    }
};
