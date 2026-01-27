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
        Schema::table('balance_transactions', function (Blueprint $table) {
            $table->string('type')->change(); // Convert enum to string for flexibility
            $table->string('currency')->default('vnd')->after('amount');
            $table->decimal('points_after', 15, 2)->default(0)->after('held_balance_after');
        });
    }

    public function down(): void
    {
        Schema::table('balance_transactions', function (Blueprint $table) {
            $table->dropColumn(['currency', 'points_after']);
            // Note: Reverting type to enum is complex and usually not done in reversible migrations
            // if data has changed, but we could try if needed.
        });
    }
};
