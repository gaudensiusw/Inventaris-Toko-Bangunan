<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pos_refunds', function (Blueprint $table) {
            // Nomor unik retur (misal: RTR-20260810-XXXX)
            $table->string('no_refund')->nullable()->after('id');
            // Kondisi barang terpisah agar bisa di-query (layak / rusak)
            $table->string('kondisi')->nullable()->after('alasan');
            // Satuan barang agar tidak hardcode "Pcs" di struk
            $table->string('satuan_nama')->nullable()->after('nama_produk');
        });
    }

    public function down(): void
    {
        Schema::table('pos_refunds', function (Blueprint $table) {
            $table->dropColumn(['no_refund', 'kondisi', 'satuan_nama']);
        });
    }
};

