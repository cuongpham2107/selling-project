<div class="px-2 mr-4">
    <x-filament::icon-button
        icon="heroicon-o-shopping-bag"
        size="md"
        tooltip="Giỏ hàng"
        x-on:click="$dispatch('open-modal', { id: 'shopping-cart' })"
    >
        @if($this->pendingCount > 0)
            <x-slot name="badge">
                <span class="fi-badge fi-size-xs">
                    {{ $this->pendingCount }}
                </span>
            </x-slot>
        @endif
    </x-filament::icon-button>

    <x-filament::modal
        id="shopping-cart"
        slide-over
        width="md"
        sticky-header
    >
        <x-slot name="heading">
            Giỏ hàng của tôi
        </x-slot>

        <div class="space-y-2">
            @forelse($this->pendingTransactions as $transaction)
                <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                    <div class="flex gap-3">
                        @if($transaction->product?->image_url)
                            <img 
                                src="{{ $transaction->product->image_url }}" 
                                alt="{{ $transaction->product->name }}"
                                class="h-24 w-24 rounded-lg object-cover"
                            >
                            @else
                            <div class="flex h-24 w-24 items-center justify-center bg-linear-to-br from-primary-500/10 to-secondary-500/10">
                                <x-heroicon-o-shopping-bag class="h-8 w-8 text-primary-500/30" />
                            </div>
                        @endif
                        <div class="flex-1 space-y-1">
                            <h4 class="font-semibold text-gray-950 dark:text-white">
                                {{ $transaction->product?->name ?? 'Sản phẩm đã xóa' }}
                            </h4>
                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                Người bán: {{ $transaction->seller?->username }}
                            </p>
                            <p class="font-semibold text-primary-600 dark:text-primary-400">
                                {{ number_format($transaction->amount, 0, ',', '.') }} VNĐ
                            </p>
                            <div class="flex items-center gap-2">
                                <x-filament::badge
                                    :color="$transaction->status->getColor()"
                                    :icon="$transaction->status->getIcon()"
                                >
                                    {{ $transaction->status->getLabel() }}
                                </x-filament::badge>
                            </div>
                        </div>
                    </div>
                    
                    @if($transaction->end_time)
                        <div class="mt-3 pt-3 border-t border-gray-200 dark:border-gray-700">
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                <span class="font-medium">Kết thúc:</span> {{ $transaction->end_time->format('d/m/Y H:i') }}
                            </p>
                        </div>
                    @endif
                </div>
            @empty
                <div class="py-12 text-center">
                    <x-filament::icon 
                        icon="heroicon-o-shopping-bag"
                        class="mx-auto h-12 w-12 text-gray-400 dark:text-gray-600"
                    />
                    <p class="mt-4 text-sm font-medium text-gray-500 dark:text-gray-400">
                        Giỏ hàng trống
                    </p>
                </div>
            @endforelse
        </div>

        @if($this->pendingTransactions->count() > 0)
            <x-slot name="footer">
                <div class="flex items-center justify-between w-full">
                    <span class="text-sm font-semibold text-gray-950 dark:text-white">
                        Tổng cộng:
                    </span>
                    <span class="text-lg font-bold text-primary-600 dark:text-primary-400">
                        {{ number_format($this->pendingTransactions->sum('amount'), 0, ',', '.') }} VNĐ
                    </span>
                </div>
            </x-slot>
        @endif
    </x-filament::modal>
</div>
