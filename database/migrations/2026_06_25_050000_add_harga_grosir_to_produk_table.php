<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('produk', function (Blueprint $table) {
            $table->boolean('aktif_grosir')->default(false)->after('harga_jual');
            $table->integer('min_qty_grosir')->nullable()->after('aktif_grosir');
            $table->bigInteger('harga_grosir')->nullable()->after('min_qty_grosir');
        });
    }

    public function down(): void
    {
        Schema::table('produk', function (Blueprint $table) {
            $table->dropColumn(['aktif_grosir', 'min_qty_grosir', 'harga_grosir']);
        });
    }
};
