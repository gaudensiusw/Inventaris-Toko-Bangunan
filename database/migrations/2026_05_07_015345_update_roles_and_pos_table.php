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
        // Add user_id to pos table if it doesn't exist
        if (!Schema::hasColumn('pos', 'user_id')) {
            Schema::table('pos', function (Blueprint $table) {
                $table->foreignId('user_id')->nullable()->after('id')->constrained('users')->nullOnDelete();
            });
        }

        // Add operator and supervisor to roles (using raw SQL for safety with enums)
        if (config('database.default') === 'mysql') {
            // First, allow both old and new roles to avoid truncation during mapping
            DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('owner', 'admin', 'kasir', 'gudang', 'supervisor', 'operator') DEFAULT 'operator'");
            
            // Map old roles to new ones
            DB::table('users')->where('role', 'admin')->update(['role' => 'supervisor']);
            DB::table('users')->where('role', 'kasir')->update(['role' => 'operator']);
            
            // Now set the final desired enum values
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
