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
        // Update existing rows
        \Illuminate\Support\Facades\DB::table('users')->where('role', 'admin')->update(['role' => 'supervisor']);
        \Illuminate\Support\Facades\DB::table('users')->where('role', 'kasir')->update(['role' => 'operator']);

        // Alter enum column
        if (config('database.default') === 'mysql') {
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('owner', 'supervisor', 'operator', 'gudang') NOT NULL DEFAULT 'operator'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert to old schema (we keep all options to avoid data loss on rollback)
        if (config('database.default') === 'mysql') {
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('owner', 'admin', 'supervisor', 'kasir', 'gudang', 'operator') NOT NULL DEFAULT 'kasir'");
        }
    }
};
