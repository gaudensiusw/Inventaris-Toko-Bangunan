<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('t_pos', function (Blueprint $table) {
            $table->id();
            $table->string('no_transaksi')->unique();
            $table->date('tgl_transaksi');
            $table->foreignId('pelanggan_id')->nullable()->constrained('m_pelanggan')->nullOnDelete();
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('pajak', 15, 2)->default(0);
            $table->decimal('total_tagihan', 15, 2)->default(0);
            $table->string('metode_pembayaran')->nullable();
            $table->string('opsi_pengiriman')->nullable();
            $table->text('catatan')->nullable();
            $table->string('status')->default('checkout'); // hold, checkout
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('t_pos');
    }
};
