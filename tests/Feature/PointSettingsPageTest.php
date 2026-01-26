<?php

use App\Filament\Pages\PointSettings;
use App\Models\BalanceTransaction;
use App\Models\Point;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

uses(RefreshDatabase::class);

test('point settings page renders correctly', function () {
    $admin = User::factory()->create();
    // Assuming simple role check or just actingAs for now

    actingAs($admin);

    $response = get(PointSettings::getUrl());

    $response->assertStatus(200);
    $response->assertSee('Thống kê Point');
    $response->assertSee('Lịch sử giao dịch Point');
});

test('point settings table shows point transactions only', function () {
    $user = User::factory()->create();

    // Create a point transaction
    BalanceTransaction::create([
        'user_id' => $user->id,
        'type' => 'point_earn',
        'amount' => 100,
        'currency' => 'point',
        'balance_after' => 0,
        'held_balance_after' => 0,
        'points_after' => 100,
        'description' => 'Visible Point Transaction',
    ]);

    // Create a VNĐ transaction
    BalanceTransaction::create([
        'user_id' => $user->id,
        'type' => 'deposit',
        'amount' => 5000,
        'currency' => 'vnd',
        'balance_after' => 5000,
        'held_balance_after' => 0,
        'points_after' => 100,
        'description' => 'Hidden VNĐ Transaction',
    ]);

    actingAs($user);

    $response = get(PointSettings::getUrl());

    $response->assertSee('Visible Point Transaction');
    $response->assertDontSee('Hidden VNĐ Transaction');
});
