<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('t_pos_detail', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pos_id')->constrained('t_pos')->onDelete('cascade');
            $table->foreignId('produk_id')->constrained('m_produk')->onDelete('cascade');
            $table->integer('qty');
            $table->decimal('harga', 15, 2);
            $table->decimal('subtotal', 15, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('t_pos_detail');
    }
};
