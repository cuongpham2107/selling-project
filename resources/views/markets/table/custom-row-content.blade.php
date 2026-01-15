@php
    $record = $getRecord();
    $seller = $record->seller;
    $categories = $record->categories;
    $viewUrl = \App\Filament\Pages\ShopProducts\MarketProductDetail::getUrl(['record' => $record->id]);
@endphp

<a href="{{ $viewUrl }}" class="block">
    <div class="group relative flex flex-col overflow-hidden rounded-lg bg-white/5 dark:bg-gray-900 shadow-sm transition-all duration-300 hover:shadow-md hover:ring-1 hover:ring-primary-500/30 ring-1 ring-white/10 cursor-pointer">
        <!-- Image Section -->
        <div class="relative aspect-square overflow-hidden">
            @if($record->image_url)
                <img src="{{ Storage::url($record->image_url) }}" alt="{{ $record->name }}" class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105">
            @else
                <div class="flex h-full w-full items-center justify-center bg-linear-to-br from-primary-500/10 to-secondary-500/10">
                    <x-heroicon-o-shopping-bag class="h-6 w-6 text-primary-500/30" />
                </div>
            @endif
            
            <!-- Badge Status -->
            <div class="absolute top-1.5 left-1.5">
                 <span class="inline-flex items-center rounded-md bg-success-500/10 px-1.5 py-0.5 text-[8px] font-bold text-blue-500 backdrop-blur-sm ring-1 ring-blue-500/20 uppercase tracking-wider">
                    Có sẵn
                 </span>
            </div>

            <!-- Category Badges -->
            <div class="absolute bottom-1.5 left-1.5 flex flex-wrap gap-1">
                @foreach($categories->take(2) as $category)
                    <span class="inline-flex items-center rounded bg-black/50 px-1.5 py-0.5 text-[8px] font-medium text-white backdrop-blur-sm">
                        {{ $category->name }}
                    </span>
                @endforeach
            </div>
        </div>

        <!-- Content Section -->
        <div class="flex flex-1 flex-col p-2.5">
            <div class="flex-1">
                <h3 class="text-xs font-semibold text-gray-800 dark:text-gray-100 line-clamp-1 group-hover:text-primary-500 transition-colors tracking-tight">
                    {{ $record->name }}
                </h3>
                <p class="mt-0.5 text-[10px] text-gray-500 dark:text-gray-400 line-clamp-2 leading-relaxed">
                    {{ $record->description ?: 'Không có mô tả cho sản phẩm.' }}
                </p>
            </div>

            <!-- Pricing & Seller -->
            <div class="mt-2.5 flex items-center justify-between border-t border-white/5 pt-2">
                <div class="flex flex-col">
                    <span class="text-[8px] font-bold text-gray-400 uppercase tracking-wider">Giá</span>
                    <span class="text-sm font-bold text-primary-500">
                        {{ number_format($record->price, 0, ',', '.') }} <small class="text-[9px] uppercase font-normal text-gray-400">đ</small>
                    </span>
                </div>
                
                <div class="flex flex-col items-end">
                    <span class="text-[8px] font-bold text-gray-400 uppercase tracking-wider">Bởi</span>
                    <div class="flex items-center gap-1">
                        <span class="text-[10px] font-medium text-gray-600 dark:text-gray-300">
                            {{ $seller->username }}
                        </span>
                        <div class="h-4 w-4 rounded-full bg-primary-500/10 flex items-center justify-center ring-1 ring-primary-500/10">
                            <x-heroicon-s-user class="h-2.5 w-2.5 text-primary-500" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</a>