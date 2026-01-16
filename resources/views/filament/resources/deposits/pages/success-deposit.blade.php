<x-filament-panels::page>
    <div class="flex items-center justify-center min-h-[400px]">
        <div class="max-w-md w-full text-center space-y-6">
            @if($errorMessage)
                <div class="flex flex-col items-center space-y-4">
                    <div class="w-20 h-20 bg-danger-100 text-danger-600 rounded-full flex items-center justify-center">
                        <x-heroicon-o-x-mark class="w-12 h-12" />
                    </div>
                    <h1 class="text-2xl font-bold tracking-tight">Thanh toán thất bại</h1>
                    <p class="text-gray-500">{{ $errorMessage }}</p>
                </div>
            @elseif($record->status === \App\Filament\Resources\Deposits\Enums\Status::Completed->value)
                <div class="flex flex-col items-center space-y-4">
                    <div class="w-20 h-20 bg-success-100 text-success-600 rounded-full flex items-center justify-center">
                        <x-heroicon-o-check class="w-12 h-12" />
                    </div>
                    <h1 class="text-2xl font-bold tracking-tight">Thanh toán thành công</h1>
                    <p class="text-gray-500">Giao dịch của bạn đã được xử lý và ví của bạn đã được cộng tiền.</p>
                    
                    <div class="w-full bg-gray-50 dark:bg-gray-900 rounded-xl p-4 text-left space-y-2">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500">Mã hóa đơn:</span>
                            <span class="font-semibold text-gray-950 dark:text-white">#{{ $record->id }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500">Số tiền:</span>
                            <span class="font-semibold text-gray-950 dark:text-white">{{ number_format($record->amount) }} VNĐ</span>
                        </div>
                        @if(isset($record->sepay_payload['data']['transactions'][0]['id']))
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500">Mã giao dịch:</span>
                            <span class="font-semibold text-gray-950 dark:text-white">{{ $record->sepay_payload['data']['transactions'][0]['id'] }}</span>
                        </div>
                        @endif
                    </div>
                </div>
            @else
                <div class="flex flex-col items-center space-y-4">
                    <div class="w-20 h-20 bg-warning-100 text-warning-600 rounded-full flex items-center justify-center">
                        <x-heroicon-o-clock class="w-12 h-12" />
                    </div>
                    <h1 class="text-2xl font-bold tracking-tight">Chờ xác nhận</h1>
                    <p class="text-gray-500">Chúng tôi đang xác nhận thanh toán của bạn. Vui lòng kiểm tra lại sau giây lát.</p>
                </div>
            @endif

            <div class="pt-4">
                <x-filament::button
                    tag="a"
                    href="{{ \App\Filament\Resources\Deposits\Pages\ListDeposits::getUrl() }}"
                    color="gray"
                    class="w-full"
                >
                    Quay lại lịch sử nạp tiền
                </x-filament::button>
            </div>
        </div>
    </div>
</x-filament-panels::page>
