<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shop_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('buyer_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('seller_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('shop_products')->onDelete('cascade');
            $table->decimal('amount', 15, 2);
            $table->decimal('fee', 15, 2);
            $table->text('content')->nullable();
            $table->enum('status', ['pending', 'held', 'completed', 'disputed', 'cancelled'])->default('pending');
            $table->foreignId('chat_id')->nullable()->constrained('chats')->onDelete('set null');
            $table->timestamp('end_time')->nullable(); // 3 ngày
            $table->timestamp('completed_at')->nullable();
            $table->text('product_data')->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index('end_time');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shop_transactions');
    }
};
