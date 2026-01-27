<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\UserBankAccount>
 */
class UserBankAccountFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $banks = [
            'Vietcombank',
            'VietinBank',
            'BIDV',
            'Agribank',
            'ACB',
            'Techcombank',
            'MB Bank',
            'VPBank',
            'TPBank',
            'Sacombank',
        ];

        return [
            'bank_name' => fake()->randomElement($banks),
            'account_holder_name' => strtoupper(fake()->name()),
            'account_number' => fake()->numerify('##########'),
            'is_default' => false,
        ];
    }

    /**
     * Indicate that this is the default bank account.
     */
    public function default(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_default' => true,
        ]);
    }
}
