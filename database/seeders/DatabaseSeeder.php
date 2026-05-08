<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Demo Accounts
        User::updateOrCreate(
            ['email' => 'owner@tokobangunan.com'],
            [
                'name' => 'Owner',
                'username' => 'owner',
                'password' => \Illuminate\Support\Facades\Hash::make('demo123'),
                'role' => 'owner',
                'aktif' => true
            ]
        );

        User::updateOrCreate(
            ['email' => 'supervisor@tokobangunan.com'],
            [
                'name' => 'Supervisor',
                'username' => 'supervisor',
                'password' => \Illuminate\Support\Facades\Hash::make('demo123'),
                'role' => 'supervisor',
                'aktif' => true
            ]
        );

        User::updateOrCreate(
            ['email' => 'operator@tokobangunan.com'],
            [
                'name' => 'Operator',
                'username' => 'operator',
                'password' => \Illuminate\Support\Facades\Hash::make('demo123'),
                'role' => 'operator',
                'aktif' => true
            ]
        );

        $this->call([
            DummyDataSeeder::class,
        ]);
    }
}
