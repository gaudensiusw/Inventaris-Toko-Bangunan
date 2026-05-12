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
        Schema::table('pos_detail', function (Blueprint $table) {
            $table->string('satuan_nama')->nullable()->after('produk_id');
            $table->decimal('isi', 15, 2)->default(1)->after('satuan_nama');
            $table->decimal('harga_satuan', 15, 2)->default(0)->after('isi');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pos_detail', function (Blueprint $table) {
            $table->dropColumn(['satuan_nama', 'isi', 'harga_satuan']);
        });
    }
};
