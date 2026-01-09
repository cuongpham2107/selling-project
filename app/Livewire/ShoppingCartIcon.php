<?php

namespace App\Livewire;

use App\Models\ShopTransaction;
use Livewire\Component;

class ShoppingCartIcon extends Component
{
    public function getListeners(): array
    {
        return [
            'cartUpdated' => '$refresh',
        ];
    }

    public function getPendingTransactionsProperty()
    {
        return ShopTransaction::query()
            ->where('buyer_id', auth()->id())
            ->whereIn('status', ['pending', 'held'])
            ->with(['product', 'seller'])
            ->latest()
            ->get();
    }

    public function getPendingCountProperty(): int
    {
        return ShopTransaction::query()
            ->where('buyer_id', auth()->id())
            ->whereIn('status', ['pending', 'held'])
            ->count();
    }

    public function render()
    {
        return view('livewire.shopping-cart-icon');
    }
}
