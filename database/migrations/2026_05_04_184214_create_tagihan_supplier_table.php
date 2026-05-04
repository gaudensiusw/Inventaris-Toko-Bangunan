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
        Schema::create('tagihan_supplier', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supplier_id')->constrained('supplier')->onDelete('cascade');
            $table->string('no_invoice');
            $table->date('tgl_invoice');
            $table->date('jatuh_tempo');
            $table->decimal('total', 15, 2);
            $table->enum('status', ['belum lunas', 'lunas', 'melewati jatuh tempo'])->default('belum lunas');
            $table->text('catatan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tagihan_supplier');
    }
};
