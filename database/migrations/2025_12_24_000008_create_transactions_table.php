<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('buyer_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('seller_id')->constrained('users')->onDelete('cascade');
            $table->text('description');
            $table->decimal('amount', 15, 2);
            $table->integer('duration'); // giờ
            $table->decimal('fee', 15, 2);
            $table->enum('status', ['pending', 'confirmed', 'seller_sent', 'buyer_received', 'completed', 'disputed', 'cancelled', 'overdue'])->default('pending');
            $table->foreignId('chat_id')->nullable()->constrained('chats')->onDelete('set null');
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('end_time')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            
            $table->index('status');
            $table->index('end_time'); // cho cron overdue
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
