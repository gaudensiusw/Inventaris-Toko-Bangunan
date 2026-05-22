<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Product\Models\Category;
use Modules\Product\Models\Product;
use Tests\TestCase;

class ProductSkuTest extends TestCase
{
    use RefreshDatabase;

    public function test_store_product_generates_auto_sku_based_on_category()
    {
        // 1. Create a category
        $category = Category::create(['nama' => 'Semen']);
        
        // 2. Create and authenticate owner user
        $user = User::create([
            'name' => 'Owner Test',
            'username' => 'ownertest',
            'email' => 'owner@example.com',
            'password' => bcrypt('password'),
            'role' => 'owner',
            'aktif' => true,
        ]);
        
        $this->actingAs($user);
        
        // 3. Post to store product with auto SKU
        $response = $this->from(route('product.index'))->post(route('product.store'), [
            'nama' => 'Semen Tiga Roda',
            'merk' => 'Tiga Roda',
            'kategori_id' => $category->id,
            'stok' => 50,
            'unit' => 'Sack',
            'harga_beli' => 60000,
            'harga_jual' => 65000,
            'min_stok' => 5,
            'sku' => '[Otomatis]',
        ]);
        
        $response->assertRedirect(route('product.index'));
        
        $this->assertDatabaseHas('produk', [
            'nama' => 'Semen Tiga Roda',
            'kategori_id' => $category->id,
            'sku' => $category->id . '00001',
        ]);
        
        // 4. Store second product in the same category
        $response2 = $this->from(route('product.index'))->post(route('product.store'), [
            'nama' => 'Semen Padang',
            'merk' => 'Padang',
            'kategori_id' => $category->id,
            'stok' => 30,
            'unit' => 'Sack',
            'harga_beli' => 58000,
            'harga_jual' => 63000,
            'min_stok' => 5,
            'sku' => '[Otomatis]',
        ]);
        
        $response2->assertRedirect(route('product.index'));
        
        $this->assertDatabaseHas('produk', [
            'nama' => 'Semen Padang',
            'kategori_id' => $category->id,
            'sku' => $category->id . '00002',
        ]);
    }

    public function test_update_product_to_auto_sku_generates_correctly()
    {
        $category = Category::create(['nama' => 'Besi']);
        
        $product = Product::create([
            'nama' => 'Besi Beton 8mm',
            'kategori_id' => $category->id,
            'stok' => 100,
            'unit' => 'Batang',
            'harga_beli' => 45000,
            'harga_jual' => 50000,
            'min_stok' => 10,
            'sku' => 'CUSTOM-SKU-999',
        ]);

        $user = User::create([
            'name' => 'Owner Test',
            'username' => 'ownertest2',
            'email' => 'owner2@example.com',
            'password' => bcrypt('password'),
            'role' => 'owner',
            'aktif' => true,
        ]);
        
        $this->actingAs($user);

        // Update product, setting SKU to blank or [Otomatis]
        $response = $this->from(route('product.index'))->put(route('product.update', $product->id), [
            'nama' => 'Besi Beton 8mm',
            'kategori_id' => $category->id,
            'stok' => 100,
            'unit' => 'Batang',
            'harga_beli' => 45000,
            'harga_jual' => 50000,
            'min_stok' => 10,
            'sku' => '[Otomatis]',
        ]);

        $response->assertRedirect(route('product.index'));

        $this->assertDatabaseHas('produk', [
            'id' => $product->id,
            'sku' => $category->id . '00001',
        ]);
    }
}
