<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('point_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->integer('amount');
            $table->enum('type', ['earn', 'send', 'receive', 'redeem']);
            $table->unsignedBigInteger('related_id')->nullable(); // Polymorphic
            $table->string('related_type')->nullable();
            $table->foreignId('recipient_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();

            $table->index(['related_id', 'related_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('point_transactions');
    }
};
