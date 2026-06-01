<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Ensure default customer "Umum" exists in table `pelanggan`
        $customer = DB::table('pelanggan')->where('kode', 'CUST-UMUM')->first();
        if (!$customer) {
            $customer_id = DB::table('pelanggan')->insertGetId([
                'kode' => 'CUST-UMUM',
                'nama' => 'Umum',
                'kategori' => 'Umum',
                'limit_kredit' => 0,
                'tenor_bayar' => 30,
                'status' => 'aktif',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } else {
            $customer_id = $customer->id;
        }

        // 2. Link all existing anonymous sales (where pelanggan_id is null or name is Umum) to the default customer
        DB::table('pos')
            ->where(function ($query) {
                $query->whereNull('pelanggan_id')
                      ->orWhere('nama_pelanggan', 'Umum')
                      ->orWhere('nama_pelanggan', 'umum');
            })
            ->update([
                'pelanggan_id' => $customer_id,
                'nama_pelanggan' => 'Umum'
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $customer = DB::table('pelanggan')->where('kode', 'CUST-UMUM')->first();
        if ($customer) {
            DB::table('pos')
                ->where('pelanggan_id', $customer->id)
                ->update(['pelanggan_id' => null]);
                
            DB::table('pelanggan')->where('id', $customer->id)->delete();
        }
    }
};
