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
        Schema::create('penggajian', function (Blueprint $table) {
            $table->id();
            $table->foreignId('karyawan_id')->constrained('karyawan')->onDelete('restrict');
            $table->date('periode_mulai');
            $table->date('periode_selesai');
            $table->date('tanggal_bayar')->nullable();
            $table->integer('jumlah_hari_kerja')->default(0);
            $table->decimal('total_gaji_pokok', 10, 2)->nullable();
            $table->decimal('total_uang_makan', 10, 2)->nullable();
            $table->decimal('total_uang_pulsa', 10, 2)->nullable();
            $table->decimal('bonus_mingguan', 10, 2)->default(0);
            $table->text('catatan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('penggajian');
    }
};
