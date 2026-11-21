<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['owner', 'kasir'])->default('kasir')->after('password');
            $table->string('no_hp', 20)->nullable()->after('role');
            $table->string('foto')->nullable()->after('no_hp');
            $table->boolean('aktif')->default(true)->after('foto');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['role', 'no_hp', 'foto', 'aktif']);
        });
    }
};
