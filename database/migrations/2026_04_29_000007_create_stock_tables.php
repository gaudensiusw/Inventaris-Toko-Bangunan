<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('t_stock_management', function (Blueprint $table) {
            $table->id();
            $table->foreignId('produk_id')->constrained('m_produk')->onDelete('cascade');
            $table->enum('tipe', ['in', 'out']);
            $table->integer('qty');
            $table->string('keterangan')->nullable();
            $table->timestamps();
        });

        Schema::create('t_stock_opname', function (Blueprint $table) {
            $table->id();
            $table->foreignId('produk_id')->constrained('m_produk')->onDelete('cascade');
            $table->integer('stok_sistem');
            $table->integer('stok_fisik');
            $table->integer('selisih');
            $table->string('keterangan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('t_stock_opname');
        Schema::dropIfExists('t_stock_management');
    }
};
