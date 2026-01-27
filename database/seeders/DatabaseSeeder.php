<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RoleUserSeeder::class,          // 1. Create roles & users FIRST
            SystemTierSeeder::class,         // 2. Create fee/point tiers
            ShopSeeder::class,               // 3. Create shop data (needs users)
            InteractionSeeder::class,        // 4. Create interactions (needs users)
            AdditionalShopProductsSeeder::class, // 5. Create more shop products
            // Note: Run shield:generate and ShieldPermissionSeeder manually after this
        ]);
    }
}
