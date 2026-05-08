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
        Schema::rename('m_kategori', 'kategori');
        Schema::rename('m_supplier', 'supplier');
        Schema::rename('m_pelanggan', 'pelanggan');
        Schema::rename('m_produk', 'produk');
        Schema::rename('t_pos', 'pos');
        Schema::rename('t_pos_detail', 'pos_detail');
        Schema::rename('t_stock_management', 'stock_management');
        Schema::rename('t_stock_opname', 'stock_opname');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::rename('kategori', 'm_kategori');
        Schema::rename('supplier', 'm_supplier');
        Schema::rename('pelanggan', 'm_pelanggan');
        Schema::rename('produk', 'm_produk');
        Schema::rename('pos', 't_pos');
        Schema::rename('pos_detail', 't_pos_detail');
        Schema::rename('stock_management', 't_stock_management');
        Schema::rename('stock_opname', 't_stock_opname');
    }
};
