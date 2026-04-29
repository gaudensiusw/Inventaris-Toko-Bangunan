<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('karyawan_id')->nullable()->constrained('karyawan')->onDelete('set null')->after('id');
            $table->string('username', 100)->unique()->after('name');
            $table->string('foto_profil', 255)->nullable()->after('password');
            $table->enum('role', ['owner', 'admin', 'kasir', 'gudang'])->default('kasir')->after('foto_profil');
            $table->boolean('aktif')->default(true)->after('role');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['karyawan_id']);
            $table->dropColumn(['karyawan_id', 'username', 'foto_profil', 'role', 'aktif']);
        });
    }
};
