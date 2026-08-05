<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pos_refunds', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('pos_id');
            $table->string('no_transaksi');
            $table->unsignedBigInteger('produk_id')->nullable();
            $table->string('nama_produk');
            $table->decimal('qty_refund', 10, 2);
            $table->decimal('nominal_refund', 15, 2);
            $table->string('alasan')->nullable();
            $table->timestamp('tgl_refund')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->timestamps();

            $table->foreign('pos_id')->references('id')->on('pos')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pos_refunds');
    }
};
