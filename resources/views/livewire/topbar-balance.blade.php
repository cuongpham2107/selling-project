<div class="flex items-center gap-3 px-3">
    {{-- Icon ví --}}
    <div class="flex items-center justify-center w-8 h-8 rounded-full bg-emerald-500 dark:bg-emerald-600 shadow-md">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5 text-white">
            <path stroke-linecap="round" stroke-linejoin="round" d="M21 12a2.25 2.25 0 0 0-2.25-2.25H15a3 3 0 1 1-6 0H5.25A2.25 2.25 0 0 0 3 12m18 0v6a2.25 2.25 0 0 1-2.25 2.25H5.25A2.25 2.25 0 0 1 3 18v-6m18 0V9M3 12V9m18 0a2.25 2.25 0 0 0-2.25-2.25H5.25A2.25 2.25 0 0 0 3 9m18 0V6a2.25 2.25 0 0 0-2.25-2.25H5.25A2.25 2.25 0 0 0 3 6v3" />
        </svg>
    </div>

    {{-- Thông tin balance --}}
    <div class="flex flex-col min-w-0">
        <div class="flex items-baseline gap-1.5">
            <span class="text-xs font-medium text-gray-600 dark:text-gray-400 uppercase tracking-wide">
                Số dư
            </span>
            @if($this->heldBalance > 0)
                <button 
                    type="button"
                    x-data="{ showTooltip: false }"
                    @mouseenter="showTooltip = true"
                    @mouseleave="showTooltip = false"
                    class="relative inline-flex items-center"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-3.5 h-3.5 text-amber-500 dark:text-amber-400">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
                    </svg>
                    {{-- Tooltip --}}
                    <div 
                        x-show="showTooltip"
                        x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="opacity-0 transform scale-95"
                        x-transition:enter-end="opacity-100 transform scale-100"
                        x-transition:leave="transition ease-in duration-150"
                        x-transition:leave-start="opacity-100 transform scale-100"
                        x-transition:leave-end="opacity-0 transform scale-95"
                        class="absolute top-full left-1/2 -translate-x-1/2 mt-2 px-3 py-2 bg-gray-900 dark:bg-gray-800 text-white text-xs rounded-lg shadow-lg whitespace-nowrap z-50"
                        style="display: none;"
                    >
                        {{-- Arrow pointing up --}}
                        <div class="absolute bottom-full left-1/2 -translate-x-1/2 -mb-px">
                            <div class="border-4 border-transparent border-b-gray-900 dark:border-b-gray-800"></div>
                        </div>
                        <div class="font-semibold mb-1">Số dư đang giữ: {{ number_format($this->heldBalance, 0, ',', '.') }} VNĐ</div>
                        <div class="text-gray-300 dark:text-gray-400 text-[10px]">Số tiền tạm giữ trong giao dịch</div>
                    </div>
                </button>
            @endif
        </div>
        <div class="font-bold text-md text-emerald-700 dark:text-emerald-400 tracking-tight">
            {{ number_format($this->availableBalance, 0, ',', '.') }}<span class="text-sm font-semibold"> VNĐ</span>
        </div>
    </div>
</div>
