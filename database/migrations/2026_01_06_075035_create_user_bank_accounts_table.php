<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('user_bank_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('bank_name'); // Tên ngân hàng (VD: Vietcombank, BIDV, etc.)
            $table->string('account_holder_name'); // Tên chủ tài khoản
            $table->string('account_number'); // Số tài khoản
            $table->boolean('is_default')->default(false); // Tài khoản mặc định
            $table->timestamps();

            // Index để tìm kiếm nhanh
            $table->index('user_id');
            $table->index(['user_id', 'is_default']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_bank_accounts');
    }
};
