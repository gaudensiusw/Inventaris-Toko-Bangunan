<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('t_pos', function (Blueprint $table) {
            $table->string('nama_pelanggan')->nullable()->after('pelanggan_id');
        });
    }

    public function down(): void
    {
        Schema::table('t_pos', function (Blueprint $table) {
            $table->dropColumn('nama_pelanggan');
        });
    }
};
