@php
    $record = $getRecord();
@endphp

<div class="group relative flex flex-col overflow-hidden rounded-lg bg-white dark:bg-gray-800 shadow-sm transition-transform duration-200 hover:shadow-md hover:-translate-y-0.5 ring-1 ring-gray-100 dark:ring-gray-700">
    <!-- Header with Bank Name and Default Badge -->
    <div class="relative p-3 border-b border-gray-100 dark:border-gray-700 bg-linear-to-r from-primary-50 to-white/40 dark:from-primary-900/10 dark:to-primary-800/10">
        <div class="flex items-center gap-3">
            <div class="flex items-center justify-center w-10 h-10 rounded-full bg-primary-500/10 dark:bg-primary-500/20 ring-1 ring-primary-100 dark:ring-primary-800">
                <x-heroicon-o-building-library class="w-5 h-5 text-primary-600 dark:text-primary-400" />
            </div>
            <div class="min-w-0">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100 truncate">
                    {{ $record->bank_name }}
                </h3>
                @if(auth()->user()->hasRole(config('filament-shield.super_admin.name')))
                    <p class="text-xs text-gray-500 dark:text-gray-400 truncate">
                        {{ $record->user->username }}
                    </p>
                @endif
            </div>
        </div>

        @if($record->is_default)
            <span class="absolute top-3 right-3 inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-success-100 text-success-800 dark:bg-success-900 dark:text-success-200">
                <x-heroicon-s-check-circle class="w-4 h-4 mr-1" />
                Mặc định
            </span>
        @endif
    </div>

    <!-- Content -->
    <div class="p-4 space-y-2">
        <!-- Account Holder Name -->
        <div class="flex items-center gap-3">
            <x-heroicon-o-user class="w-4 h-4 text-gray-400 shrink-0" />
            <div class="flex-1 min-w-0">
                <p class="text-xs text-gray-500 dark:text-gray-400">Chủ tài khoản</p>
                <p class="text-sm font-medium text-gray-900 dark:text-gray-100 truncate">
                    {{ $record->account_holder_name }}
                </p>
            </div>
        </div>

        <!-- Account Number -->
        <div class="flex items-center gap-3">
            <x-heroicon-o-credit-card class="w-4 h-4 text-gray-400 shrink-0" />
            <div class="flex-1 min-w-0">
                <p class="text-xs text-gray-500 dark:text-gray-400">Số tài khoản</p>
                <div class="flex items-center gap-2">
                    <p class="font-mono text-sm font-semibold text-primary-600 dark:text-primary-400 truncate">
                        {{ $record->account_number }}
                    </p>
                    <button 
                        onclick="navigator.clipboard.writeText('{{ $record->account_number }}'); 
                                 window.$wireui && window.$wireui.notify({title: 'Đã sao chép!', icon: 'success'}) || alert('Đã sao chép số tài khoản!')"
                        class="text-gray-400 hover:text-primary-600 dark:hover:text-primary-400 transition-colors"
                        title="Sao chép số tài khoản"
                    >
                        <x-heroicon-o-clipboard-document class="w-4 h-4" />
                    </button>
                </div>
            </div>
        </div>

        <!-- Created Date -->
        <div class="flex items-center gap-3 pt-1">
            <x-heroicon-o-calendar class="w-4 h-4 text-gray-400 shrink-0" />
            <div class="flex-1 min-w-0">
                <p class="text-xs text-gray-500 dark:text-gray-400">Ngày tạo</p>
                <p class="text-sm text-gray-700 dark:text-gray-300">
                    {{ $record->created_at->format('d/m/Y H:i') }}
                </p>
            </div>
        </div>
    </div>

    <!-- Footer Actions (Optional - shown on hover) -->
    <div class="px-4 py-2 bg-transparent opacity-0 group-hover:opacity-100 transition-opacity">
        <div class="flex items-center justify-end gap-2">
            <span class="text-xs text-gray-500 dark:text-gray-400">
                Cập nhật: {{ $record->updated_at->diffForHumans() }}
            </span>
        </div>
    </div>
</div>
