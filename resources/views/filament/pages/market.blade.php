<x-filament-panels::page>
    @php
        $categories = \App\Models\ShopCategory::query()
            ->withCount([
                'products' => function ($query) {
                    $query->where('status', 'active');
                }
            ])
            ->get();
    @endphp
    
    <x-filament::tabs :contained="true" x-data="{ activeTab: 'all' }">
        {{-- Tab "Tất cả" --}}
        <x-filament::tabs.item
            alpine-active="activeTab === 'all'"
            x-on:click="activeTab = 'all'; $wire.filterByCategory(null)"
            icon="heroicon-o-squares-2x2"
        >
            Tất cả
            
            <x-slot name="badge">
                {{ \App\Models\ShopProduct::query()->where('status', 'active')->count() }}
            </x-slot>
        </x-filament::tabs.item>

        {{-- Tabs cho từng category --}}
        @foreach($categories as $category)
            <x-filament::tabs.item
                alpine-active="activeTab === 'category-{{ $category->id }}'"
                x-on:click="activeTab = 'category-{{ $category->id }}'; $wire.filterByCategory({{ $category->id }})"
                icon="{{ $category->icon }}"
            >
                {{ $category->name }}
                
                <x-slot name="badge">
                    {{ $category->products_count }}
                </x-slot>
            </x-filament::tabs.item>
        @endforeach
    </x-filament::tabs>
    
    {{ $this->table }}
</x-filament-panels::page>
