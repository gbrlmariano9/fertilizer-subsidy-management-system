<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoUserSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Demo Farmer account
        $farmer = User::firstOrNew([
            'email' => 'farmer@gmail.com',
        ]);

        $farmer->forceFill([
            'name' => 'Juan Dela Cruz',
            'password' => Hash::make('Password123!'),
            'email_verified_at' => now(),
            'role' => 'farmer',
            'beneficiary_id' => 'FARMER-001',
            'subsidy_balance' => 10000.00,
            'wallet_balance' => 0,
            'is_verified' => true,
            'is_active' => true,
        ])->save();

        // 2. Demo Merchant account
        $merchant = User::firstOrNew([
            'email' => 'merchant@gmail.com',
        ]);

        $merchant->forceFill([
            'name' => 'Agri Supply',
            'password' => Hash::make('Password123!'),
            'email_verified_at' => now(),
            'role' => 'merchant',
            'beneficiary_id' => null,
            'subsidy_balance' => 0,
            'wallet_balance' => 0,
            'is_verified' => true,
            'is_active' => true,
        ])->save();

        // 3. Demo fertilizer products
        Product::updateOrCreate(
            ['sku' => 'FERT-N-001'],
            [
                'merchant_id' => $merchant->id,
                'name' => 'High Nitrogen Fertilizer',
                'description' => 'Demo high-nitrogen fertilizer, 1 bag.',
                'price' => 1000.00,
                'stock' => 100,
                'is_active' => true,
            ]
        );

        Product::updateOrCreate(
            ['sku' => 'FERT-NPK-001'],
            [
                'merchant_id' => $merchant->id,
                'name' => 'Balanced NPK Fertilizer',
                'description' => 'Demo balanced NPK fertilizer, 1 bag.',
                'price' => 1500.00,
                'stock' => 50,
                'is_active' => true,
            ]
        );
    }
}
