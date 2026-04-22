<?php

namespace Modules\Product\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Product\Models\Product;
use Modules\Supplier\Models\Supplier;

class ProductDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $suppliers = Supplier::all();

        if ($suppliers->isEmpty()) {
            return;
        }

        $categories = ['Material Dasar', 'Pipa & Plumbing', 'Cat & Pelapis', 'Besi & Logam'];
        $units = ['kg', 'pcs', 'meter', 'sak'];

        foreach ($suppliers as $supplier) {
            for ($i = 1; $i <= 3; $i++) {
                Product::create([
                    'supplier_id' => $supplier->id,
                    'name' => $supplier->company_name . ' Item ' . $i,
                    'sku' => strtoupper(substr($supplier->company_name, 0, 3)) . '-' . rand(100, 999),
                    'category' => $categories[array_rand($categories)],
                    'stock' => rand(10, 500),
                    'unit' => $units[array_rand($units)],
                    'purchase_price' => rand(10000, 500000),
                    'selling_price' => rand(550000, 1000000),
                    'min_stock' => 20
                ]);
            }
        }
    }
}
