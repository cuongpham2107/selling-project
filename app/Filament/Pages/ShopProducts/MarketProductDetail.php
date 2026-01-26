<?php

namespace App\Filament\Pages\ShopProducts;

use App\Filament\Actions\BuyProductAction;
use App\Models\ShopProduct;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;

class MarketProductDetail extends Page implements HasActions
{
    use InteractsWithActions;

    protected string $view = 'filament.pages.shop-products.market-product-detail';

    protected static bool $shouldRegisterNavigation = false;

    public ?ShopProduct $record = null;

    public function mount(): void
    {
        $recordId = request()->query('record') ?? request()->route('record');

        $this->record = ShopProduct::with(['seller', 'categories', 'transactions'])
            ->findOrFail($recordId);
    }

    public function getTitle(): string|Htmlable
    {
        return $this->record?->name ?? 'Chi tiết sản phẩm';
    }

    protected function getActions(): array
    {
        return [
            BuyProductAction::make()
                ->record($this->record),
        ];
    }
}
