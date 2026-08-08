<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pos', function (Blueprint $table) {
            $table->decimal('biaya_addon', 15, 2)->default(0)->after('ongkos_kirim');
            $table->string('keterangan_addon')->nullable()->after('biaya_addon');
        });
    }

    public function down(): void
    {
        Schema::table('pos', function (Blueprint $table) {
            $table->dropColumn(['biaya_addon', 'keterangan_addon']);
        });
    }
};
