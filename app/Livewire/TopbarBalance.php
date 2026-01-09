<?php

namespace App\Livewire;

use Livewire\Component;

class TopbarBalance extends Component
{
    public function getListeners(): array
    {
        return [
            'balanceUpdated' => '$refresh',
            'cartUpdated' => '$refresh',
        ];
    }

    public function getAvailableBalanceProperty()
    {
        return auth()->user()?->balance?->balance ?? 0;
    }

    public function getHeldBalanceProperty()
    {
        return auth()->user()?->balance?->held_balance ?? 0;
    }

    public function render()
    {
        return view('livewire.topbar-balance');
    }
}
