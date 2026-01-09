<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fee_tiers', function (Blueprint $table) {
            $table->id();
            $table->decimal('min_amount', 15, 2)->nullable();
            $table->decimal('max_amount', 15, 2)->nullable();
            $table->decimal('fee', 15, 2);
            $table->enum('type', ['middle', 'shop'])->default('middle');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fee_tiers');
    }
};
