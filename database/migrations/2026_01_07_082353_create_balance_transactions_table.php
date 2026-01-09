<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * This table tracks ALL balance changes for users, providing a complete transaction history.
     * It uses polymorphic relationships to link to the source transaction.
     */
    public function up(): void
    {
        Schema::create('balance_transactions', function (Blueprint $table) {
            $table->id();
            
            // User this transaction belongs to
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            
            // Transaction type (what kind of balance change)
            $table->enum('type', [
                'deposit',              // Nạp tiền
                'withdrawal',           // Rút tiền
                'purchase',             // Mua hàng (trừ tiền)
                'sale',                 // Bán hàng (cộng tiền)
                'hold',                 // Giữ tiền (chuyển từ balance → held_balance)
                'release',              // Giải phóng tiền (chuyển từ held_balance → balance)
                'refund',               // Hoàn tiền
                'point_redeem',         // Đổi điểm ra tiền
                'fee',                  // Phí giao dịch
                'dispute_refund',       // Hoàn tiền từ tranh chấp
                'dispute_payout',       // Thanh toán từ tranh chấp
                'middleman_purchase',   // Mua qua trung gian
                'middleman_sale',       // Bán qua trung gian
            ]);
            
            // Amount (positive = tiền vào, negative = tiền ra)
            $table->decimal('amount', 15, 2);
            
            // Balance after this transaction
            $table->decimal('balance_after', 15, 2);
            
            // Held balance after this transaction
            $table->decimal('held_balance_after', 15, 2)->default(0);
            
            // Polymorphic relationship to source (Deposit, Withdrawal, ShopTransaction, Transaction, etc.)
            $table->nullableMorphs('source');
            
            // Optional: Reference to related user (e.g., buyer/seller in transactions)
            $table->foreignId('related_user_id')->nullable()->constrained('users')->onDelete('set null');
            
            // Description/Note
            $table->text('description')->nullable();
            
            // Metadata (JSON for extra info like product name, etc.)
            $table->json('metadata')->nullable();
            
            $table->timestamps();
            
            // Indexes for fast queries
            $table->index('user_id');
            $table->index('type');
            $table->index(['source_id', 'source_type']);
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('balance_transactions');
    }
};
