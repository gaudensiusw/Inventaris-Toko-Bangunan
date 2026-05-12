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
            $table->decimal('bonus_tetap', 12, 2)->default(0)->change();
        });

        // Update existing records to have 0 bonus
        \Illuminate\Support\Facades\DB::table('karyawan')->update(['bonus_tetap' => 0]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('karyawan', function (Blueprint $table) {
            $table->decimal('bonus_tetap', 12, 2)->default(500000)->change();
        });
    }
};
