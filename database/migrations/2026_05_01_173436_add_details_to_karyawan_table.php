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
        Schema::table('karyawan', function (Blueprint $table) {
            $table->string('kode_karyawan')->unique()->nullable()->after('id');
            $table->string('email')->nullable()->after('no_hp');
            $table->decimal('bonus_tetap', 12, 2)->default(500000)->after('aktif');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('karyawan', function (Blueprint $table) {
            $table->dropColumn(['kode_karyawan', 'email', 'bonus_tetap']);
        });
    }
};
