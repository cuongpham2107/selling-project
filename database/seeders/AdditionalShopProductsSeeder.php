<?php

namespace Database\Seeders;

use App\Models\ShopCategory;
use App\Models\ShopProduct;
use App\Models\User;
use Illuminate\Database\Seeder;

class AdditionalShopProductsSeeder extends Seeder
{
    public function run(): void
    {
        // Get ONLY panel_user role users
        $users = User::role('panel_user')->get();
        
        if ($users->isEmpty()) {
            $this->command->warn('⚠️  Not enough panel_user users to create additional shop products.');
            return;
        }

        $categories = ShopCategory::all();
        if ($categories->isEmpty()) {
            return;
        }

        $products = [
            [
                'name' => 'Tài khoản ChatGPT Plus 1 tháng',
                'description' => 'Tài khoản ChatGPT Plus chính chủ, truy cập GPT-4o, DALL-E 3.',
                'price' => 450000,
                'stock' => "chatgpt_user1:pass123\nchatgpt_user2:pass456",
                'category_names' => ['Dịch vụ Giải trí', 'Phần mềm / Key'],
            ],
            [
                'name' => 'Youtube Premium 1 năm (Gia hạn email)',
                'description' => 'Gia hạn trực tiếp vào email của bạn, không quảng cáo, Youtube Music.',
                'price' => 350000,
                'stock' => 'Vui lòng liên hệ sau khi mua để gửi email gia hạn.',
                'category_names' => ['Dịch vụ Giải trí'],
            ],
            [
                'name' => 'Adobe Creative Cloud Full Apps 1 năm',
                'description' => 'Bản quyền Photoshop, Illustrator, Premiere Pro... 100GB Cloud.',
                'price' => 1200000,
                'stock' => 'license-key-adobe-xxxx-yyyy',
                'category_names' => ['Công cụ Design', 'Phần mềm / Key'],
            ],
            [
                'name' => 'Tài khoản TradingView Pro+ 1 tháng',
                'description' => 'Hỗ trợ phân tích kỹ thuật chuyên sâu, không quảng cáo, nhiều biểu đồ.',
                'price' => 250000,
                'stock' => 'tradingview_pro_user:pwd999',
                'category_names' => ['Phần mềm / Key'],
            ],
            [
                'name' => 'Gói Canva Pro 1 năm (Team)',
                'description' => 'Sử dụng đầy đủ tính năng thiết kế cao cấp của Canva.',
                'price' => 150000,
                'stock' => 'https://canva.com/brand/join?token=abc-xyz',
                'category_names' => ['Công cụ Design'],
            ],
            [
                'name' => 'Tài khoản Midjourney Basic Plan',
                'description' => 'Trình tạo ảnh AI mạnh mẽ nhất hiện nay.',
                'price' => 300000,
                'stock' => 'midjourney_account:mj_pass_123',
                'category_names' => ['Công cụ Design', 'Dịch vụ Giải trí'],
            ],
            [
                'name' => 'Microsoft 365 Family 1 năm (1 slot)',
                'description' => 'Office Full Apps + 1TB OneDrive chính chủ.',
                'price' => 280000,
                'stock' => 'microsoft365_invite_link_xxxx',
                'category_names' => ['Phần mềm / Key'],
            ],
            [
                'name' => 'Tài khoản Grammarly Premium 1 tháng',
                'description' => 'Kiểm tra ngữ pháp và đạo văn chuyên nghiệp.',
                'price' => 180000,
                'stock' => 'grammarly_premium_acc:pass_987',
                'category_names' => ['Phần mềm / Key'],
            ],
            [
                'name' => 'Code Steam Wallet 20$',
                'description' => 'Nạp trực tiếp vào ví Steam để mua game.',
                'price' => 480000,
                'stock' => 'STEAM-WALLET-20-XXXX-YYYY',
                'category_names' => ['Tài khoản Game'],
            ],
            [
                'name' => 'Tài khoản Roblox 10,000 Robux',
                'description' => 'Tài khoản trắng thông tin kèm sẵn Robux.',
                'price' => 850000,
                'stock' => 'roblox_user:robux_pass_secret',
                'category_names' => ['Tài khoản Game'],
            ],
        ];

        foreach ($products as $pData) {
            $product = ShopProduct::create([
                'user_id' => $users->random()->id,
                'name' => $pData['name'],
                'description' => $pData['description'],
                'price' => $pData['price'],
                'stock' => $pData['stock'],
                'status' => 'active',
            ]);

            $categoryIds = $categories->whereIn('name', $pData['category_names'])->pluck('id');
            $product->categories()->attach($categoryIds);
        }
    }
}
