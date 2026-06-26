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
        // 1. Index untuk tabel 'pos' (sangat sering di-query berdasarkan tanggal/status/user)
        Schema::table('pos', function (Blueprint $table) {
            $table->index('created_at');
            $table->index('status_pembayaran');
            $table->index('tgl_transaksi');
        });

        // 2. Index untuk tabel 'produk' (sering di-search dan filter)
        Schema::table('produk', function (Blueprint $table) {
            $table->index('nama');
            $table->index('stok');
        });

        // 3. Index untuk tabel 'stock_opname' (sering di-filter status pending untuk notifikasi)
        Schema::table('stock_opname', function (Blueprint $table) {
            $table->index('status');
            $table->index('created_at');
        });

        // 4. Index untuk tabel 'tagihan_supplier' (sering di-filter status dan jatuh tempo)
        Schema::table('tagihan_supplier', function (Blueprint $table) {
            $table->index('status');
            $table->index('jatuh_tempo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pos', function (Blueprint $table) {
            $table->dropIndex(['created_at']);
            $table->dropIndex(['status_pembayaran']);
            $table->dropIndex(['tgl_transaksi']);
        });

        Schema::table('produk', function (Blueprint $table) {
            $table->dropIndex(['nama']);
            $table->dropIndex(['stok']);
        });

        Schema::table('stock_opname', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropIndex(['created_at']);
        });

        Schema::table('tagihan_supplier', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropIndex(['jatuh_tempo']);
        });
    }
};
