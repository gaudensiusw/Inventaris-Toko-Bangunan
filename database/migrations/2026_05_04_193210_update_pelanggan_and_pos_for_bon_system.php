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
        Schema::table('pelanggan', function (Blueprint $table) {
            $table->string('kode')->unique()->after('id')->nullable();
            $table->string('email')->after('nama')->nullable();
            $table->decimal('limit_kredit', 15, 2)->default(0)->after('alamat');
            $table->integer('tenor_bayar')->default(30)->after('limit_kredit'); // in days
            $table->enum('status', ['aktif', 'nonaktif'])->default('aktif')->after('tenor_bayar');
        });

        Schema::table('pos', function (Blueprint $table) {
            $table->decimal('jumlah_bayar', 15, 2)->default(0)->after('total_tagihan');
            $table->date('jatuh_tempo')->nullable()->after('jumlah_bayar');
            $table->enum('status_pembayaran', ['lunas', 'belum_bayar', 'sebagian'])->default('lunas')->after('jatuh_tempo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pelanggan', function (Blueprint $table) {
            $table->dropColumn(['kode', 'email', 'limit_kredit', 'tenor_bayar', 'status']);
        });

        Schema::table('pos', function (Blueprint $table) {
            $table->dropColumn(['jumlah_bayar', 'jatuh_tempo', 'status_pembayaran']);
        });
    }
};
