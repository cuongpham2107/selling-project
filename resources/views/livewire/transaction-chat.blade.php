<div class="flex flex-col h-175 bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden" wire:poll.5s>
    <!-- Messages Container -->
    <div class="flex-1 overflow-y-auto p-4 space-y-4 bg-white dark:bg-gray-900 custom-scrollbar" id="chat-messages">
        @forelse($messages as $message)
            @php $isMe = $message->sender_id === auth()->id(); @endphp
            <div class="flex {{ $isMe ? 'justify-end' : 'justify-start' }} items-end gap-2 group mb-1">
                @if(!$isMe)
                    <div class="w-8 h-8 rounded-full bg-gray-200 dark:bg-gray-700 flex items-center justify-center text-gray-600 dark:text-gray-300 text-xs font-bold shrink-0 overflow-hidden" title="{{ $message->sender?->username }}">
                        @if($message->sender?->avatar_url)
                            <img src="{{ $message->sender->avatar_url }}" class="w-full h-full object-cover">
                        @else
                            {{ substr($message->sender?->username ?? '?', 0, 1) }}
                        @endif
                    </div>
                @endif

                <div class="flex flex-col {{ $isMe ? 'items-end' : 'items-start' }} max-w-[75%]">
                    @if($message->image_url)
                        <div class="mb-1">
                            <img src="{{ $message->image_url }}" class="rounded-xl max-w-full h-auto shadow-sm cursor-pointer hover:opacity-90 transition-opacity" 
                                 onclick="window.open('{{ $message->image_url }}', '_blank')">
                        </div>
                    @endif

                    @if($message->content)
                        <div class="px-4 py-2.5 text-[15px] leading-relaxed shadow-sm {{ $isMe ? 'bg-primary-600 text-white rounded-2xl rounded-tr-lg' : 'bg-gray-100 dark:bg-gray-800 text-gray-900 dark:text-gray-100 rounded-2xl rounded-bl-lg' }}">
                            {{ $message->content }}
                        </div>
                    @endif
                    
                    @if($message->created_at)
                    <span class="text-[10px] text-gray-400 mt-1 px-1 opacity-0 group-hover:opacity-100 transition-opacity duration-200">
                        {{ $message->created_at->format('H:i') }}
                    </span>
                    @endif
                </div>
            </div>
        @empty
            <div class="flex flex-col items-center justify-center h-full text-gray-400 space-y-4 opacity-70">
                <div class="w-16 h-16 bg-gray-50 dark:bg-gray-800 rounded-full flex items-center justify-center">
                    <x-heroicon-o-chat-bubble-oval-left-ellipsis class="w-8 h-8 text-gray-300" />
                </div>
                <p class="text-sm font-medium text-gray-500">Bắt đầu cuộc trò chuyện</p>
            </div>
        @endforelse
    </div>

    <!-- Image Preview Area -->
    @if($image)
        <div class="p-2 px-4 border-t border-gray-100 dark:border-gray-800 bg-gray-50 dark:bg-gray-800/50 flex items-center gap-3">
            <div class="relative w-16 h-16 rounded-lg overflow-hidden border border-gray-200 dark:border-gray-700 shadow-sm">
                <img src="{{ $image->temporaryUrl() }}" class="w-full h-full object-cover">
                <button wire:click="$set('image', null)" class="absolute top-0 right-0 bg-red-500 text-white rounded-bl-lg p-0.5 hover:bg-red-600">
                    <x-heroicon-s-x-mark class="w-4 h-4" />
                </button>
            </div>
            <div class="text-xs text-gray-500 italic">
                Sẵn sàng gửi ảnh...
            </div>
        </div>
    @endif

    <!-- Error Messages -->
    @error('image')
        <div class="px-4 py-1 text-xs text-red-500 bg-red-50 dark:bg-red-900/20 italic">
            {{ $message }}
        </div>
    @enderror

    <!-- Input Area -->
    <div class="p-3 border-t border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900">
        <input type="file" id="chat-image-input" wire:model="image" class="hidden" accept="image/*">
        
        <x-filament::input.wrapper>
            <x-filament::input
                type="textarea"
                wire:model="newMessage"
                placeholder="Nhập tin nhắn..."
                wire:keydown.enter="sendMessage"
                :disabled="!$chatId"
            />
            <x-slot name="suffix">
                <div class="flex items-center gap-1">
                    <div wire:loading wire:target="image" class="mr-2">
                        <x-filament::loading-indicator class="w-5 h-5" />
                    </div>
                    <x-filament::icon-button
                        icon="heroicon-s-paper-airplane"
                        wire:click="sendMessage"
                        :disabled="!$chatId"
                        color="primary"
                    />
                </div>
            </x-slot>
            <x-slot name="prefix">
                <x-filament::icon-button
                    icon="heroicon-s-paper-clip"
                    :disabled="!$chatId"
                    color="gray"
                    onclick="document.getElementById('chat-image-input').click()"
                    title="Đính kèm ảnh (Tối đa 1MB, 3 ảnh/ngày)"
                />
            </x-slot>
        </x-filament::input.wrapper>
    </div>

    <script>
        function scrollToBottom() {
            const container = document.getElementById('chat-messages');
            if (container) {
                container.scrollTop = container.scrollHeight;
            }
        }

        document.addEventListener('livewire:initialized', () => {
            scrollToBottom();

            Livewire.on('messageSent', () => {
                setTimeout(scrollToBottom, 50);
            });
        });

        // Cuộn xuống sau mỗi lần polling nếu đang ở gần cuối
        document.addEventListener('livewire:navigated', scrollToBottom);
    </script>

    <style>
        .custom-scrollbar::-webkit-scrollbar {
            width: 4px;
        }
        .custom-scrollbar::-webkit-scrollbar-track {
            background: rgba(0,0,0,0.05);
        }
        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: rgba(0,0,0,0.1);
            border-radius: 10px;
        }
    </style>
</div>
