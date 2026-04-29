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
        Schema::create('item_operasional', function (Blueprint $table) {
            $table->id();
            $table->foreignId('karyawan_id')->constrained('karyawan')->onDelete('restrict');
            $table->string('nama', 255);
            $table->string('kategori', 100)->nullable();
            $table->decimal('jumlah', 10, 2);
            $table->string('satuan', 50)->nullable();
            $table->decimal('harga', 10, 2);
            $table->decimal('total_harga', 10, 2)->storedAs('jumlah * harga');
            $table->text('deskripsi')->nullable();
            $table->date('tanggal_penggunaan')->nullable();
            $table->date('tanggal_pembelian')->nullable();
            $table->enum('status', ['aktif', 'habis', 'rusak'])->default('aktif');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('item_operasional');
    }
};
