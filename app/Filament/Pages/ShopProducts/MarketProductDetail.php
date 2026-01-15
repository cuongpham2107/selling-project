<?php

namespace App\Filament\Pages\ShopProducts;

use App\Models\ShopProduct;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Pages\Page;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\TextSize;
use Illuminate\Contracts\Support\Htmlable;

class MarketProductDetail extends Page
{
    protected string $view = 'filament.pages.shop-products.market-product-detail';

    protected static bool $shouldRegisterNavigation = false;

    public ?ShopProduct $record = null;

    public function mount(): void
    {
        $recordId = request()->query('record') ?? request()->route('record');
        
        $this->record = ShopProduct::with(['seller', 'categories', 'transactions'])
            ->findOrFail($recordId);
    }

    public function getTitle(): string | Htmlable
    {
        return $this->record?->name ?? 'Chi tiết sản phẩm';
    }
}
