<?php

namespace Database\Seeders;

use App\Models\Dispute;
use App\Models\ShopCategory;
use App\Models\ShopProduct;
use App\Models\ShopTransaction;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ShopSeeder extends Seeder
{
    public function run(): void
    {
        // Get ONLY panel_user role users (regular users, not admins/staff)
        $users = User::role('panel_user')->get();
        
        if ($users->count() < 2) {
            $this->command->warn('⚠️  Not enough panel_user users to create shop data. Need at least 2 panel_user accounts.');
            return;
        }

        // Sellers are also panel_user (regular users selling to other users)
        $sellers = $users->take(min(3, $users->count()));

        // 1. Create Categories
        $categories = [
            ['name' => 'Tài khoản Game', 'icon' => 'heroicon-o-device-phone-mobile'],
            ['name' => 'Phần mềm / Key', 'icon' => 'heroicon-o-key'],
            ['name' => 'Dịch vụ Giải trí', 'icon' => 'heroicon-o-play'],
            ['name' => 'Công cụ Design', 'icon' => 'heroicon-o-pencil-square'],
        ];

        $categoryModels = collect();
        foreach ($categories as $cat) {
            $categoryModels->push(ShopCategory::updateOrCreate(
                ['slug' => Str::slug($cat['name'])],
                [
                    'name' => $cat['name'],
                    'icon' => $cat['icon'],
                    'is_active' => true,
                ]
            ));
        }

        // 2. Create Products
        $products = [
            ['name' => 'Tài khoản Netflix 1 tháng', 'price' => 50000, 'stock' => "user1:pass1\nuser2:pass2", 'cats' => ['Dịch vụ Giải trí']],
            ['name' => 'Tài khoản Spotify Premium', 'price' => 30000, 'stock' => "code123\ncode456", 'cats' => ['Dịch vụ Giải trí']],
            ['name' => 'Key Windows 11 Pro', 'price' => 150000, 'stock' => 'XXXXX-XXXXX-XXXXX-XXXXX', 'cats' => ['Phần mềm / Key']],
            ['name' => 'Code Game Steam 10$', 'price' => 220000, 'stock' => 'STEAM-ABC-DEF', 'cats' => ['Tài khoản Game']],
            ['name' => 'Tài khoản Canva Pro vĩnh viễn', 'price' => 100000, 'stock' => "invite-link-1\ninvite-link-2", 'cats' => ['Công cụ Design', 'Phần mềm / Key']],
        ];

        foreach ($products as $p) {
            $product = ShopProduct::updateOrCreate(
                ['name' => $p['name']],
                [
                    'user_id' => $sellers->random()->id,
                    'description' => 'Mô tả chi tiết cho sản phẩm kỹ thuật số '.$p['name'].'. Giao hàng tự động 24/7.',
                    'price' => $p['price'],
                    'stock' => $p['stock'],
                    'status' => 'active',
                ]
            );

            // Attach Categories
            $catIds = $categoryModels->whereIn('name', $p['cats'])->pluck('id');
            $product->categories()->sync($catIds);

            // 3. Create some transactions for each product
            for ($j = 0; $j < 2; $j++) {
                $buyer = $users->where('id', '!=', $product->user_id)->random();

                $shopTransaction = ShopTransaction::create([
                    'buyer_id' => $buyer->id,
                    'seller_id' => $product->user_id,
                    'product_id' => $product->id,
                    'amount' => $product->price,
                    'fee' => $product->price * 0.05, // 5% fee example
                    'status' => ['pending', 'completed', 'disputed', 'held'][rand(0, 3)],
                ]);

                if ($shopTransaction->status === 'disputed' && ! Dispute::where('transaction_id', $shopTransaction->id)->where('transaction_type', ShopTransaction::class)->exists()) {
                    Dispute::create([
                        'transaction_type' => ShopTransaction::class,
                        'transaction_id' => $shopTransaction->id,
                        'initiator_id' => $buyer->id,
                        'reason' => 'Tài khoản không đăng nhập được, yêu cầu đổi mới hoặc hoàn tiền.',
                        'status' => 'pending',
                    ]);
                }
            }
        }
    }
}
