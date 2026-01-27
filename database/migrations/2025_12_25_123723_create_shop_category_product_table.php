<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shop_category_product', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_category_id')->constrained('shop_categories')->onDelete('cascade');
            $table->foreignId('shop_product_id')->constrained('shop_products')->onDelete('cascade');
            $table->timestamps();

            $table->unique(['shop_category_id', 'shop_product_id'], 'category_product_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shop_category_product');
    }
};
