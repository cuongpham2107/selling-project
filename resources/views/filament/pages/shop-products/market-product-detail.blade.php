<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Product Image and Basic Info --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            {{-- Image --}}
            <div class="md:col-span-1">
                @if($record->image_url)
                    <img src="{{ Storage::url($record->image_url) }}" 
                         alt="{{ $record->name }}" 
                         class="w-full h-96 object-cover rounded-lg shadow-lg">
                @else
                    <div class="w-full h-96 bg-linear-to-br from-primary-500/10 to-secondary-500/10 rounded-lg shadow-lg flex items-center justify-center">
                        <x-heroicon-o-shopping-bag class="w-24 h-24 text-primary-500/30" />
                    </div>
                @endif
            </div>

            {{-- Product Details --}}
            <div class="md:col-span-2 space-y-6">
                {{-- Name and Price --}}
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
                        {{ $record->name }}
                    </h1>
                    <div class="mt-4 flex items-baseline gap-4">
                        <span class="text-4xl font-bold text-primary-600 dark:text-primary-400">
                            {{ number_format($record->price, 0, ',', '.') }}đ
                        </span>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                            {{ $record->status === 'active' ? 'bg-success-100 text-success-800 dark:bg-success-900 dark:text-success-200' : 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200' }}">
                            {{ match($record->status) {
                                'active' => 'Đang hoạt động',
                                'sold' => 'Đã bán',
                                'deleted' => 'Đã xóa',
                                'banned' => 'Đã khóa',
                                default => $record->status
                            } }}
                        </span>
                    </div>
                </div>

                {{-- Description --}}
                @if($record->description)
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Mô tả sản phẩm</h3>
                        <div class="prose dark:prose-invert max-w-none">
                            {!! str($record->description)->markdown() !!}
                        </div>
                    </div>
                @endif

                {{-- Categories --}}
                @if($record->categories->count() > 0)
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Danh mục</h3>
                        <div class="flex flex-wrap gap-2">
                            @foreach($record->categories as $category)
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-info-100 text-info-800 dark:bg-info-900 dark:text-info-200">
                                    {{ $category->name }}
                                </span>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- Seller Info --}}
                <div class="border-t border-gray-200 dark:border-gray-700 pt-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Thông tin người bán</h3>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <span class="text-sm text-gray-500 dark:text-gray-400">Tên người bán</span>
                            <p class="text-base font-medium text-gray-900 dark:text-white">
                                <x-heroicon-s-user class="inline w-5 h-5 text-primary-500 mr-1" />
                                {{ $record->seller->username }}
                            </p>
                        </div>
                        <div>
                            <span class="text-sm text-gray-500 dark:text-gray-400">Email</span>
                            <p class="text-base font-medium text-gray-900 dark:text-white">
                                <x-heroicon-s-envelope class="inline w-5 h-5 text-primary-500 mr-1" />
                                {{ $record->seller->email }}
                            </p>
                        </div>
                        <div>
                            <span class="text-sm text-gray-500 dark:text-gray-400">Ngày đăng</span>
                            <p class="text-base font-medium text-gray-900 dark:text-white">
                                <x-heroicon-s-calendar class="inline w-5 h-5 text-primary-500 mr-1" />
                                {{ $record->created_at->format('d/m/Y H:i') }}
                            </p>
                        </div>
                        <div>
                            <span class="text-sm text-gray-500 dark:text-gray-400">Cập nhật lần cuối</span>
                            <p class="text-base font-medium text-gray-900 dark:text-white">
                                <x-heroicon-s-clock class="inline w-5 h-5 text-primary-500 mr-1" />
                                {{ $record->updated_at->format('d/m/Y H:i') }}
                            </p>
                        </div>
                    </div>
                </div>

                {{-- Action Buttons --}}
                <div class="flex gap-4">
                    <x-filament::button
                        tag="a"
                        :href="route('filament.admin.pages.market')"
                        color="gray"
                        icon="heroicon-o-arrow-left"
                    >
                        Quay lại Market
                    </x-filament::button>
                    
                    @if($record->status === 'active')
                        {{-- Add Buy button here if needed --}}
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-filament-panels::page>

