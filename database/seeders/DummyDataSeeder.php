<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Product\Models\Category;
use Modules\Supplier\Models\Supplier;
use Modules\Customer\Models\Customer;
use Modules\Product\Models\Product;

class DummyDataSeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            'Material Dasar',
            'Pipa & Plumbing',
            'Cat & Pelapis',
            'Besi & Logam'
        ];

        foreach ($categories as $cat) {
            Category::create(['nama' => $cat]);
        }

        $suppliers = [
            ['company_name' => 'PT Semen Indonesia', 'phone' => '021-123456', 'address' => 'Gresik'],
            ['company_name' => 'CV Karya Besi', 'phone' => '021-654321', 'address' => 'Jakarta'],
            ['company_name' => 'TB Makmur Jaya', 'phone' => '021-789012', 'address' => 'Bandung']
        ];

        foreach ($suppliers as $sup) {
            Supplier::create($sup);
        }

        $customers = [
            ['nama' => 'Budi Santoso', 'telp' => '08123456789', 'alamat' => 'Jl. Merdeka No. 1'],
            ['nama' => 'Siti Aminah', 'telp' => '08987654321', 'alamat' => 'Jl. Mawar No. 5']
        ];

        foreach ($customers as $cus) {
            Customer::create($cus);
        }

        $products = [
            [
                'nama' => 'Semen Portland - Gresik',
                'sku' => 'SEM-001',
                'kategori_id' => 1,
                'supplier_id' => 1,
                'stok' => 100,
                'unit' => 'Sak',
                'min_stok' => 10,
                'harga_beli' => 65000,
                'harga_jual' => 75000,
            ],
            [
                'nama' => 'Besi Beton 10mm',
                'sku' => 'BES-010',
                'kategori_id' => 4,
                'supplier_id' => 2,
                'stok' => 50,
                'unit' => 'Batang',
                'min_stok' => 5,
                'harga_beli' => 90000,
                'harga_jual' => 105000,
            ],
            [
                'nama' => 'Pipa PVC 1/2 Inch',
                'sku' => 'PIP-002',
                'kategori_id' => 2,
                'supplier_id' => 3,
                'stok' => 30,
                'unit' => 'Batang',
                'min_stok' => 5,
                'harga_beli' => 15000,
                'harga_jual' => 20000,
            ]
        ];

        foreach ($products as $prod) {
            Product::create($prod);
        }
    }
}
