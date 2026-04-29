<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('m_produk', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->foreignId('kategori_id')->nullable()->constrained('m_kategori')->nullOnDelete();
            $table->foreignId('supplier_id')->nullable()->constrained('m_supplier')->nullOnDelete();
            $table->integer('stok')->default(0);
            $table->string('unit')->default('pcs');
            $table->integer('min_stok')->default(0);
            $table->decimal('harga_beli', 15, 2)->default(0);
            $table->decimal('harga_jual', 15, 2)->default(0);
            $table->string('sku')->unique()->nullable();
            $table->string('image')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('m_produk');
    }
};
