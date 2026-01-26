<?php

namespace App\Filament\Auth\Pages;

use App\Models\User;
use Closure;
use Filament\Auth\Pages\Register as BaseAuth;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Schema;

class Register extends BaseAuth
{
    protected static string $layout = 'filament.components.layout.simple';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                $this->getNameFormComponent(),
                $this->getEmailFormComponent(),
                $this->getReferralCodeFormComponent(),
                $this->getPasswordFormComponent(),
                $this->getPasswordConfirmationFormComponent(),

            ]);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeRegister(array $data): array
    {
        $data['referral_code'] = strtoupper(trim($data['referral_code'] ?? ''));

        return $data;
    }

    protected function getReferralCodeFormComponent(): Component
    {
        return TextInput::make('referral_code')
            ->label('Mã giới thiệu')
            ->afterLabel('Nhập mã giới thiệu của bạn (Nếu có)')
            ->default(request()->query('ref'))
            ->disabled(fn () => request()->has('ref'))
            ->dehydrated()
            ->live()
            ->rules([
                fn (): Closure => function (string $attribute, $value, Closure $fail) {
                    // Nếu không nhập mã giới thiệu thì bỏ qua validation
                    if (empty($value)) {
                        return;
                    }

                    $check_exists = User::query()->where('referral_code', $value)->exists();
                    if (! $check_exists) {
                        $fail('Mã giới thiệu không hợp lệ.');
                    }
                },
            ])
            ->maxLength(255);
    }
}
