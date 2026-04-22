<?php

namespace Modules\Supplier\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Modules\Supplier\Models\Supplier;
use Modules\Product\Models\Product;

class SupplierDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        $suppliers = [
            [
                'company_name' => 'PT Semen Indonesia',
                'contact_person' => 'Budi Santoso',
                'phone' => '0812-3456-7890',
                'email' => 'contact@semenindonesia.com',
                'address' => 'Jl. Veteran No. 1, Gresik',
                'city' => 'Gresik',
                'province' => 'Jawa Timur'
            ],
            [
                'company_name' => 'CV Karya Besi',
                'contact_person' => 'Agus Pratama',
                'phone' => '0821-9876-5432',
                'email' => 'sales@karyabesi.id',
                'address' => 'Kawasan Industri Pulogadung',
                'city' => 'Jakarta Timur',
                'province' => 'DKI Jakarta'
            ],
            [
                'company_name' => 'TB Makmur Jaya',
                'contact_person' => 'Siti Aminah',
                'phone' => '0857-1122-3344',
                'email' => 'info@makmurjaya.com',
                'address' => 'Jl. Magelang Km 5',
                'city' => 'Sleman',
                'province' => 'DI Yogyakarta'
            ],
            [
                'company_name' => 'PT Cat Sentosa',
                'contact_person' => 'Rina Wijaya',
                'phone' => '0813-5566-7788',
                'email' => 'cs@catsentosa.co.id',
                'address' => 'Jl. Rungkut Industri',
                'city' => 'Surabaya',
                'province' => 'Jawa Timur'
            ]
        ];

        foreach ($suppliers as $data) {
            Supplier::create($data);
        }
    }
}
