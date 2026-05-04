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
        Schema::table('tagihan_supplier', function (Blueprint $table) {
            $table->enum('status', ['belum_bayar', 'cicilan', 'lunas'])->default('belum_bayar')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tagihan_supplier', function (Blueprint $table) {
            $table->enum('status', ['belum lunas', 'lunas', 'melewati jatuh tempo'])->default('belum lunas')->change();
        });
    }
};
