<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add user_id to pos table
        Schema::table('pos', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->after('id')->constrained('users')->nullOnDelete();
        });

        // Add operator and supervisor to roles (using raw SQL for safety with enums)
        if (config('database.default') === 'mysql') {
            DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('owner', 'supervisor', 'operator') DEFAULT 'operator'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pos', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
        });

        if (config('database.default') === 'mysql') {
            DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('owner', 'admin', 'kasir', 'gudang') DEFAULT 'kasir'");
        }
    }
};
