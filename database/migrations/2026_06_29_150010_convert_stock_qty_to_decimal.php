<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Mengubah tipe kolom stok dan qty dari INTEGER menjadi DECIMAL(10,2)
     * agar sistem mendukung kuantitas pecahan (contoh: 1.5 meter, 0.5 kg).
     * 
     * Operasi ini AMAN dan tidak akan menghapus data yang sudah ada.
     * Data integer yang lama (contoh: 15) akan otomatis menjadi 15.00.
     */
    public function up(): void
    {
        // 1. Tabel produk: kolom stok dan min_stok
        Schema::table('produk', function (Blueprint $table) {
            $table->decimal('stok', 10, 2)->default(0)->change();
            $table->decimal('min_stok', 10, 2)->default(0)->change();
        });

        // 2. Tabel pos_detail: kolom qty (kuantitas di transaksi kasir)
        Schema::table('pos_detail', function (Blueprint $table) {
            $table->decimal('qty', 10, 2)->change();
        });

        // 3. Tabel stock_management: kolom qty (riwayat keluar/masuk stok manual)
        Schema::table('stock_management', function (Blueprint $table) {
            $table->decimal('qty', 10, 2)->change();
        });

        // 4. Tabel stock_opname: kolom stok_sistem, stok_fisik, selisih
        Schema::table('stock_opname', function (Blueprint $table) {
            $table->decimal('stok_sistem', 10, 2)->change();
            $table->decimal('stok_fisik', 10, 2)->change();
            $table->decimal('selisih', 10, 2)->change();
        });
    }

    /**
     * Rollback: mengembalikan semua kolom ke tipe INT.
     * Catatan: Jika ada data desimal saat rollback, angka akan dibulatkan otomatis oleh MySQL.
     */
    public function down(): void
    {
        Schema::table('produk', function (Blueprint $table) {
            $table->integer('stok')->default(0)->change();
            $table->integer('min_stok')->default(0)->change();
        });

        Schema::table('pos_detail', function (Blueprint $table) {
            $table->integer('qty')->change();
        });

        Schema::table('stock_management', function (Blueprint $table) {
            $table->integer('qty')->change();
        });

        Schema::table('stock_opname', function (Blueprint $table) {
            $table->integer('stok_sistem')->change();
            $table->integer('stok_fisik')->change();
            $table->integer('selisih')->change();
        });
    }
};
