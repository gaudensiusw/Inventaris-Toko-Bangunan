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
        Schema::table('supplier', function (Blueprint $table) {
            $table->renameColumn('nama', 'company_name');
            $table->renameColumn('telp', 'phone');
            $table->renameColumn('alamat', 'address');
        });

        Schema::table('supplier', function (Blueprint $table) {
            $table->string('contact_person')->nullable()->after('company_name');
            $table->string('email')->nullable()->after('phone');
            $table->string('city')->nullable()->after('email');
            $table->string('province')->nullable()->after('city');
        });

        Schema::table('pelanggan', function (Blueprint $table) {
            $table->string('kategori')->nullable()->after('nama');
        });
    }

    public function down(): void
    {
        Schema::table('supplier', function (Blueprint $table) {
            $table->dropColumn(['contact_person', 'email', 'city', 'province']);
            $table->renameColumn('company_name', 'nama');
            $table->renameColumn('phone', 'telp');
            $table->renameColumn('address', 'alamat');
        });

        Schema::table('pelanggan', function (Blueprint $table) {
            $table->dropColumn('kategori');
        });
    }
};
