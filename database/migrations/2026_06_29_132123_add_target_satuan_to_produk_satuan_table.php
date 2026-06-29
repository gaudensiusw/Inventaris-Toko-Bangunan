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
        Schema::table('produk_satuan', function (Blueprint $table) {
            $table->string('target_satuan')->nullable()->after('nama');
            $table->decimal('target_isi', 15, 2)->nullable()->after('target_satuan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('produk_satuan', function (Blueprint $table) {
            $table->dropColumn(['target_satuan', 'target_isi']);
        });
    }
};
