<div 
    class="flex h-[calc(100vh-10rem)] bg-white dark:bg-gray-900 rounded-xl overflow-hidden shadow-lg border border-gray-200 dark:border-gray-800"
    x-data="chatBox()"
    x-init="init()"
>
    
    <!-- SIDEBAR (Chat List) - Hidden on mobile when chat selected -->
    <div 
        class="w-full md:w-1/3 lg:w-1/4 border-r border-gray-200 dark:border-gray-800 flex flex-col bg-gray-50 dark:bg-gray-900/50"
        :class="{ 'hidden md:flex': @js($selectedChat !== null) }"
    >
        <!-- Search Header -->
        <div class="p-3 md:p-4 border-b border-gray-200 dark:border-gray-800">
            <div class="flex items-center justify-between mb-3 md:mb-4">
                <h2 class="text-lg md:text-xl font-bold text-gray-800 dark:text-gray-100">Chats</h2>
                <button 
                    wire:click="openNewChatModal"
                    class="p-1.5 md:p-2 bg-gray-900 hover:bg-gray-800 dark:bg-gray-800 dark:hover:bg-gray-700 text-white rounded-lg transition-colors shadow-sm"
                    title="Tạo chat mới"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 md:w-5 md:h-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                </button>
            </div>
            <div class="relative">
                <input 
                    type="text" 
                    wire:model.live.debounce.300ms="search" 
                    placeholder="Search..." 
                    class="w-full pl-9 md:pl-10 pr-4 py-2 text-xs md:text-sm rounded-lg border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 focus:ring-primary-500 focus:border-primary-500"
                >
                <div class="absolute inset-y-0 left-0 pl-2.5 md:pl-3 flex items-center pointer-events-none">
                    <svg class="h-4 w-4 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" />
                    </svg>
                </div>
            </div>
        </div>

        <!-- Conversation List -->
        <div class="flex-1 overflow-y-auto custom-scrollbar">
            @forelse($this->chats as $chatItem)
                <div 
                    wire:click="selectChat({{ $chatItem->id }})"
                    class="cursor-pointer p-3 md:p-4 flex items-center space-x-2 md:space-x-3 hover:bg-white dark:hover:bg-gray-800 transition-colors {{ $selectedChat && $selectedChat->id === $chatItem->id ? 'bg-white dark:bg-gray-800 border-l-4 border-gray-900 dark:border-gray-700 shadow-sm' : 'border-l-4 border-transparent' }}"
                >
                    <!-- Avatar -->
                    <div class="shrink-0 relative">
                        @if($chatItem->type === 'general')
                            <div class="w-10 h-10 md:w-12 md:h-12 rounded-full bg-gray-200 dark:bg-gray-700 flex items-center justify-center text-gray-900 dark:text-gray-100">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5 md:w-6 md:h-6">
                                    <path fill-rule="evenodd" d="M4.804 21.644A6.707 6.707 0 006 21.75a6.721 6.721 0 003.583-1.029c.774.182 1.584.279 2.417.279 5.322 0 9.75-3.97 9.75-9 0-5.03-4.428-9-9.75-9s-9.75 3.97-9.75 9c0 2.409 1.025 4.587 2.915 6.109l2.179 2.535z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        @else
                            @php $avatar = $this->getChatAvatar($chatItem); @endphp
                            @if($avatar)
                                <img src="{{ $avatar }}" class="w-10 h-10 md:w-12 md:h-12 rounded-full object-cover">
                            @else
                                <div class="w-10 h-10 md:w-12 md:h-12 rounded-full bg-gray-200 dark:bg-gray-700 flex items-center justify-center text-gray-500 font-bold text-base md:text-lg">
                                    {{ substr($this->getChatName($chatItem), 0, 1) }}
                                </div>
                            @endif
                        @endif
                        
                        <!-- Unread Badge -->
                        @php $unreadCount = $this->getChatUnreadCount($chatItem); @endphp
                        @if($unreadCount > 0)
                            <div class="absolute -top-0.5 -right-0.5 md:-top-1 md:-right-1 bg-red-500 text-white text-[10px] md:text-xs font-bold rounded-full w-4 h-4 md:w-5 md:h-5 flex items-center justify-center">
                                {{ $unreadCount > 9 ? '9+' : $unreadCount }}
                            </div>
                        @endif
                    </div>

                    <!-- Info -->
                    <div class="flex-1 min-w-0">
                        <div class="flex justify-between items-baseline mb-0.5 md:mb-1">
                            <h3 class="text-xs md:text-sm font-semibold text-gray-900 dark:text-gray-100 truncate">
                                {{ $this->getChatName($chatItem) }}
                            </h3>
                            @if($lastMsg = $chatItem->messages->first())
                                <span class="text-[10px] md:text-xs text-gray-400">{{ $lastMsg->created_at->format('H:i') }}</span>
                            @endif
                        </div>
                        <p class="text-xs md:text-sm text-gray-500 dark:text-gray-400 truncate">
                            @if($lastMsg = $chatItem->messages->first())
                                <span class="{{ $lastMsg->sender_id === auth()->id() ? '' : 'font-medium text-gray-700 dark:text-gray-300' }}">
                                    {{ $lastMsg->sender_id === auth()->id() ? 'You: ' : '' }}{{ $lastMsg->content }}
                                </span>
                            @else
                                <span class="italic text-gray-400">Không có tin nhắn</span>
                            @endif
                        </p>
                    </div>
                </div>
            @empty
                <div class="p-4 text-center text-gray-500 text-sm">
                    Không tìm thấy cuộc trò chuyện nào.
                </div>
            @endforelse
        </div>
    </div>


    <!-- MAIN CHAT AREA -->
    <div 
        class="flex-1 flex flex-col bg-white dark:bg-gray-900 w-full md:w-2/3 lg:w-3/4"
        :class="{ 'hidden md:flex': @js($selectedChat === null) }"
    >
        @if($selectedChat)
            <!-- Chat Header -->
            <div class="px-3 md:px-6 py-3 md:py-4 border-b border-gray-200 dark:border-gray-800 flex items-center justify-between bg-white dark:bg-gray-900 z-10">
                <div class="flex items-center space-x-2 md:space-x-3">
                    <!-- Back button for mobile -->
                    <button 
                        wire:click="deselectChat"
                        class="md:hidden p-1.5 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-full text-gray-600 dark:text-gray-400"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" />
                        </svg>
                    </button>
                    
                    <div class="shrink-0">
                        @if($selectedChat->type === 'general')
                            <div class="w-8 h-8 md:w-10 md:h-10 rounded-full bg-gray-200 dark:bg-gray-700 flex items-center justify-center text-gray-900 dark:text-gray-100">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-4 h-4 md:w-5 md:h-5">
                                    <path fill-rule="evenodd" d="M4.804 21.644A6.707 6.707 0 006 21.75a6.721 6.721 0 003.583-1.029c.774.182 1.584.279 2.417.279 5.322 0 9.75-3.97 9.75-9 0-5.03-4.428-9-9.75-9s-9.75 3.97-9.75 9c0 2.409 1.025 4.587 2.915 6.109l2.179 2.535z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        @else
                            @php $avatar = $this->getChatAvatar($selectedChat); @endphp
                            @if($avatar)
                                <img src="{{ $avatar }}" class="w-8 h-8 md:w-10 md:h-10 rounded-full object-cover">
                            @else
                                <div class="w-8 h-8 md:w-10 md:h-10 rounded-full bg-gray-200 dark:bg-gray-700 flex items-center justify-center text-gray-500 font-bold text-sm md:text-base">
                                    {{ substr($this->getChatName($selectedChat), 0, 1) }}
                                </div>
                            @endif
                        @endif
                    </div>
                    <div>
                        <h2 class="text-sm md:text-lg font-bold text-gray-800 dark:text-gray-100">
                            {{ $this->getChatName($selectedChat) }}
                        </h2>
                        @if($selectedChat->type === 'general')
                            <p class="text-[10px] md:text-xs text-green-500 flex items-center">
                                <span class="w-2 h-2 bg-green-500 rounded-full mr-1"></span> Online
                            </p>
                        @endif
                    </div>
                </div>
                <div class="flex items-center space-x-2">
                    <!-- Search Toggle -->
                    <div class="relative" x-data="{ showSearch: false }">
                        <button 
                            @click="showSearch = !showSearch; if(!showSearch) $wire.set('messageSearch', '')" 
                            class="p-1.5 md:p-2 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-full text-gray-400 transition-colors"
                            :class="{ 'text-gray-900 dark:text-gray-100 bg-gray-100 dark:bg-gray-800': showSearch }"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 md:w-6 md:h-6">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                            </svg>
                        </button>
                        
                        <!-- Search Input (appears when button clicked) -->
                        <div 
                            x-show="showSearch" 
                            x-transition:enter="transition ease-out duration-200"
                            x-transition:enter-start="opacity-0 scale-95"
                            x-transition:enter-end="opacity-100 scale-100"
                            x-transition:leave="transition ease-in duration-150"
                            x-transition:leave-start="opacity-100 scale-100"
                            x-transition:leave-end="opacity-0 scale-95"
                            class="absolute right-0 top-12 z-50"
                            @click.away="showSearch = false"
                            style="display: none;"
                        >
                            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl border border-gray-200 dark:border-gray-700 p-3 w-72">
                                <div class="relative">
                                    <input 
                                        type="text" 
                                        wire:model.live.debounce.300ms="messageSearch"
                                        placeholder="Tìm kiếm tin nhắn..." 
                                        class="w-full pl-10 pr-4 py-2 rounded-lg border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-sm focus:ring-gray-900 focus:border-gray-900 dark:focus:ring-gray-700 dark:focus:border-gray-700"
                                        x-init="$nextTick(() => $el.focus())"
                                    >
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <svg class="h-4 w-4 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                    @if($messageSearch)
                                        <button 
                                            wire:click="$set('messageSearch', '')"
                                            class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 dark:hover:text-gray-300"
                                        >
                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-4 h-4">
                                                <path d="M6.28 5.22a.75.75 0 00-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 101.06 1.06L10 11.06l3.72 3.72a.75.75 0 101.06-1.06L11.06 10l3.72-3.72a.75.75 0 00-1.06-1.06L10 8.94 6.28 5.22z" />
                                            </svg>
                                        </button>
                                    @endif
                                </div>
                                @if($messageSearch)
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">
                                        Đang tìm kiếm: "{{ $messageSearch }}"
                                    </p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Messages List -->
            <div class="flex-1 overflow-y-auto p-2 md:p-4 space-y-4 md:space-y-6 flex flex-col scroll-smooth bg-gray-50/50 dark:bg-gray-900" id="chat-messages" x-init="$el.scrollTop = $el.scrollHeight">
                @forelse($this->selectedChatMessages as $message)
                    <div class="group w-full flex {{ $message->sender_id === auth()->id() ? 'justify-end' : 'justify-start' }}">
                        <div class="flex max-w-[85%] md:max-w-[80%] lg:max-w-[70%] gap-2 md:gap-3 {{ $message->sender_id === auth()->id() ? 'flex-row-reverse' : 'flex-row' }}">
                            <!-- Avatar -->
                            <div class="shrink-0 flex flex-col justify-end">
                                <div 
                                    class="w-6 h-6 md:w-8 md:h-8 rounded-full flex items-center justify-center {{ $message->sender_id === auth()->id() ? 'bg-gray-900 dark:bg-gray-800' : 'bg-gray-300 dark:bg-gray-700' }} text-white text-[10px] md:text-xs font-bold overflow-hidden cursor-pointer hover:ring-2 hover:ring-gray-900 dark:hover:ring-gray-700 transition-all"
                                    @contextmenu.prevent="$wire.showContextMenu({{ $message->sender_id }}, $event.clientX, $event.clientY)"
                                >
                                     @if($message->sender_id === auth()->id())
                                        <!-- User Icon -->
                                         <span class="text-xs">M</span>
                                    @else
                                        @if($message->sender && $message->sender->avatar_url)
                                             <img src="{{ $message->sender->avatar_url }}" class="w-full h-full object-cover">
                                        @else
                                            {{ substr($message->sender->name ?? '?', 0, 1) }}
                                        @endif
                                    @endif
                                </div>
                            </div>

                            <!-- Bubble -->
                            <div class="flex flex-col {{ $message->sender_id === auth()->id() ? 'items-end' : 'items-start' }}">
                                <div class="flex items-end gap-2">
                                     @if($message->sender_id !== auth()->id())
                                        <span class="text-[10px] md:text-xs text-gray-500 mb-0.5 md:mb-1 ml-1">{{ $message->sender->name ?? 'Unknown' }}</span>
                                     @endif
                                </div>
                                <div class="px-3 py-2 md:px-4 md:py-2.5 rounded-2xl shadow-sm text-xs md:text-sm leading-relaxed {{ 
                                    $message->sender_id === auth()->id() 
                                        ? 'bg-gray-900 dark:bg-gray-800 text-white rounded-br-none' 
                                        : 'bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-800 dark:text-gray-100 rounded-bl-none' 
                                }}">
                                    {{ $message->content }}
                                </div>
                                <div class="flex items-center gap-1 mt-1 px-1">
                                    <span class="text-[10px] text-gray-400 opacity-0 group-hover:opacity-100 transition-opacity">
                                        {{ $message->created_at->format('H:i') }}
                                    </span>
                                    @if($message->sender_id === auth()->id())
                                        <!-- Read status for sent messages -->
                                        @if($message->read_at)
                                            <!-- Double check mark - Message read -->
                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-4 h-4 text-blue-500" title="Đã đọc {{ $message->read_at->format('H:i') }}">
                                                <path d="M0.41,13.41L6,19L7.41,17.58L1.83,12M22.24,5.58L11.66,16.17L7.5,12L6.07,13.41L11.66,19L23.66,7M18,7L16.59,5.58L10.24,11.93L11.66,13.34L18,7Z" />
                                            </svg>
                                        @else
                                            <!-- Single check mark - Message sent -->
                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-4 h-4 text-gray-400" title="Đã gửi">
                                                <path d="M21,7L9,19L3.5,13.5L4.91,12.09L9,16.17L19.59,5.59L21,7Z" />
                                            </svg>
                                        @endif
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="flex flex-col items-center justify-center h-full text-gray-400 space-y-4">
                        <div class="w-16 h-16 bg-gray-100 dark:bg-gray-800 rounded-full flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-8 h-8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8.625 12a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H8.25m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H12m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 01-2.555-.337A5.972 5.972 0 015.41 20.97a5.969 5.969 0 01-.474-.065 4.48 4.48 0 00.978-2.025c.09-.457-.133-.901-.467-1.226C3.93 16.178 3 14.159 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25z" />
                            </svg>
                        </div>
                        <p class="text-sm">Chưa có tin nhắn nào</p>
                    </div>
                @endforelse
            </div>

            <!-- Input Area -->
            <div class="p-2 md:p-4 bg-white dark:bg-gray-900 border-t border-gray-200 dark:border-gray-800">
                <form wire:submit="sendMessage" class="relative flex items-center gap-1 md:gap-2">
                    <button type="button" class="p-1.5 md:p-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors">
                         <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 md:w-6 md:h-6">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M18.375 12.739l-7.693 7.693a4.5 4.5 0 01-6.364-6.364l10.94-10.94A3 3 0 1119.5 7.372L8.552 18.32m.009-.01l-.01.01m5.699-9.941l-7.81 7.81a1.5 1.5 0 002.112 2.13" />
                        </svg>
                    </button>
                    <input 
                        type="text" 
                        wire:model="content" 
                        placeholder="Type a message..." 
                        class="flex-1 rounded-full border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 px-3 py-2 md:px-4 md:py-3 text-xs md:text-sm focus:ring-gray-900 focus:border-gray-900 dark:focus:ring-gray-700 dark:focus:border-gray-700 placeholder-gray-400 dark:text-white"
                        required
                    >
                    <button 
                        type="submit" 
                        class="p-1.5 md:p-2 bg-gray-900 hover:bg-gray-800 dark:bg-gray-800 dark:hover:bg-gray-700 text-white rounded-full transition-colors shadow-sm disabled:opacity-50"
                        wire:loading.attr="disabled"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-4 h-4 md:w-5 md:h-5 transform -rotate-25">
                            <path d="M3.478 2.405a.75.75 0 00-.926.94l2.432 7.905H13.5a.75.75 0 010 1.5H4.984l-2.432 7.905a.75.75 0 00.926.94 60.519 60.519 0 0018.445-8.986.75.75 0 000-1.218A60.517 60.517 0 003.478 2.405z" />
                        </svg>
                    </button>
                </form>
            </div>
        @else
            <!-- Empty State for No Selection -->
            <div class="flex-1 flex flex-col items-center justify-center bg-white dark:bg-gray-900 text-gray-500">
                <div class="w-24 h-24 bg-gray-100 dark:bg-gray-800 rounded-full flex items-center justify-center mb-6">
                     <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-12 h-12 text-gray-900 dark:text-gray-100">
                      <path fill-rule="evenodd" d="M4.804 21.644A6.707 6.707 0 006 21.75a6.721 6.721 0 003.583-1.029c.774.182 1.584.279 2.417.279 5.322 0 9.75-3.97 9.75-9 0-5.03-4.428-9-9.75-9s-9.75 3.97-9.75 9c0 2.409 1.025 4.587 2.915 6.109l2.179 2.535z" clip-rule="evenodd" />
                    </svg>
                </div>
                <h3 class="text-xl font-bold mb-2">Welcome to Messages</h3>
                <p class="text-gray-400">Select a conversation to start chatting.</p>
            </div>
        @endif
    </div>

    <!-- Context Menu -->
    @if($contextMenuUserId)
        <div 
            class="fixed inset-0 z-40"
            wire:click="hideContextMenu"
            x-data="{ show: @entangle('contextMenuUserId').live }"
            x-show="show"
            x-transition.opacity
        >
            <div 
                class="fixed bg-white dark:bg-gray-800 rounded-lg shadow-2xl border border-gray-200 dark:border-gray-700 py-2 min-w-50 z-50"
                style="left: {{ $contextMenuPosition['x'] }}px; top: {{ $contextMenuPosition['y'] }}px;"
                wire:click.stop
                x-data
                x-init="
                    $nextTick(() => {
                        const rect = $el.getBoundingClientRect();
                        const windowWidth = window.innerWidth;
                        const windowHeight = window.innerHeight;
                        
                        // Adjust horizontal position if menu goes off screen
                        if (rect.right > windowWidth) {
                            $el.style.left = ({{ $contextMenuPosition['x'] }} - rect.width) + 'px';
                        }
                        
                        // Adjust vertical position if menu goes off screen
                        if (rect.bottom > windowHeight) {
                            $el.style.top = ({{ $contextMenuPosition['y'] }} - rect.height) + 'px';
                        }
                    })
                "
            >
                <!-- <button 
                    wire:click="viewProfile({{ $contextMenuUserId }})"
                    class="w-full px-4 py-2 text-left text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 flex items-center gap-3 transition-colors"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17.982 18.725A7.488 7.488 0 0012 15.75a7.488 7.488 0 00-5.982 2.975m11.963 0a9 9 0 10-11.963 0m11.963 0A8.966 8.966 0 0112 21a8.966 8.966 0 01-5.982-2.275M15 9.75a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    Xem Hồ Sơ
                </button> -->

                @if($contextMenuUserId !== auth()->id())
                     <!-- private chat -->
                    <button 
                        wire:click="startPrivateChat({{ $contextMenuUserId }})"
                        class="w-full px-4 py-2 text-left text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 flex items-center gap-3 transition-colors"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 8.511c.884.284 1.5 1.128 1.5 2.097v4.286c0 1.136-.847 2.1-1.98 2.193-.34.027-.68.052-1.02.072v3.091l-3-3c-1.354 0-2.694-.055-4.02-.163a2.115 2.115 0 01-.825-.242m9.345-8.334a2.126 2.126 0 00-.476-.095 48.64 48.64 0 00-8.048 0c-1.131.094-1.976 1.057-1.976 2.192v4.286c0 .837.46 1.58 1.155 1.951m9.345-8.334V6.637c0-1.621-1.152-3.026-2.76-3.235A48.455 48.455 0 0011.25 3c-2.115 0-4.198.137-6.24.402-1.608.209-2.76 1.614-2.76 3.235v6.226c0 1.621 1.152 3.026 2.76 3.235.577.075 1.157.14 1.74.194V21l4.155-4.155" />
                        </svg>
                        Nhắn Tin Riêng
                    </button>

                    <!-- Create Middle Transaction -->
                    <button 
                        wire:click="createMiddleTransaction({{ $contextMenuUserId }})"
                        class="w-full px-4 py-2 text-left text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 flex items-center gap-3 transition-colors"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5zm-12 0h.008v.008H6V10.5z" />
                        </svg>
                        Tạo Giao Dịch Trung Gian
                    </button>

                    <!-- <div class="border-t border-gray-200 dark:border-gray-700 my-1"></div> -->

                    <!-- <button 
                        wire:click="blockUser({{ $contextMenuUserId }})"
                        class="w-full px-4 py-2 text-left text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 flex items-center gap-3 transition-colors"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                        </svg>
                        Chặn Người Dùng
                    </button> -->

                    <!-- <button 
                        wire:click="reportUser({{ $contextMenuUserId }})"
                        class="w-full px-4 py-2 text-left text-sm text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 flex items-center gap-3 transition-colors"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 3v1.5M3 21v-6m0 0l2.77-.693a9 9 0 016.208.682l.108.054a9 9 0 006.086.71l3.114-.732a48.524 48.524 0 01-.005-10.499l-3.11.732a9 9 0 01-6.085-.711l-.108-.054a9 9 0 00-6.208-.682L3 4.5M3 15V4.5" />
                        </svg>
                        Báo Cáo
                    </button> -->
                @endif
            </div>
        </div>
    @endif

    <!-- Modal Tạo Chat Mới -->
    @if($showNewChatModal)
        <div class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4" wire:click="closeNewChatModal">
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-2xl w-full max-w-md max-h-[80vh] flex flex-col" @click.stop>
                <!-- Modal Header -->
                <div class="flex items-center justify-between p-6 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-xl font-bold text-gray-800 dark:text-gray-100">Tạo Chat Mới</h3>
                    <button 
                        wire:click="closeNewChatModal"
                        class="p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- Search User -->
                <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                    <div class="relative">
                        <input 
                            type="text" 
                            wire:model.live.debounce.300ms="userSearch" 
                            placeholder="Tìm kiếm người dùng..." 
                            class="w-full pl-10 pr-4 py-2 rounded-lg border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-sm focus:ring-gray-900 focus:border-gray-900 dark:focus:ring-gray-700 dark:focus:border-gray-700"
                        >
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-4 w-4 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" />
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- User List -->
                <div class="flex-1 overflow-y-auto p-4 space-y-2">
                    @forelse($this->availableUsers as $user)
                        <div 
                            wire:click="selectUser({{ $user->id }})"
                            class="cursor-pointer p-3 flex items-center gap-3 hover:bg-gray-50 dark:hover:bg-gray-700 rounded-lg transition-colors {{ $selectedUserId === $user->id ? 'bg-gray-100 dark:bg-gray-800 border-2 border-gray-900 dark:border-gray-700' : 'border-2 border-transparent' }}"
                        >
                            <div class="shrink-0">
                                @if($user->avatar_url)
                                    <img src="{{ $user->avatar_url }}" class="w-10 h-10 rounded-full object-cover">
                                @else
                                    <div class="w-10 h-10 rounded-full bg-gray-200 dark:bg-gray-600 flex items-center justify-center text-gray-600 dark:text-gray-300 font-bold">
                                        {{ substr($user->username, 0, 1) }}
                                    </div>
                                @endif
                            </div>
                            <div class="flex-1 min-w-0">
                                <h4 class="text-sm font-semibold text-gray-800 dark:text-gray-100 truncate">
                                    {{ $user->username }}
                                </h4>
                                <p class="text-xs text-gray-500 dark:text-gray-400 truncate">
                                    {{ $user->email }}
                                </p>
                            </div>
                            @if($selectedUserId === $user->id)
                                <div class="shrink-0">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-6 h-6 text-gray-900 dark:text-gray-100">
                                        <path fill-rule="evenodd" d="M2.25 12c0-5.385 4.365-9.75 9.75-9.75s9.75 4.365 9.75 9.75-4.365 9.75-9.75 9.75S2.25 17.385 2.25 12zm13.36-1.814a.75.75 0 10-1.22-.872l-3.236 4.53L9.53 12.22a.75.75 0 00-1.06 1.06l2.25 2.25a.75.75 0 001.14-.094l3.75-5.25z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                            @endif
                        </div>
                    @empty
                        <div class="text-center py-8">
                            <div class="w-16 h-16 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center mx-auto mb-3">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-8 h-8 text-gray-400">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                                </svg>
                            </div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Không tìm thấy người dùng</p>
                        </div>
                    @endforelse
                </div>

                <!-- Modal Footer -->
                <div class="flex gap-3 p-6 border-t border-gray-200 dark:border-gray-700">
                    <button 
                        wire:click="closeNewChatModal"
                        class="flex-1 px-4 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors font-medium"
                    >
                        Hủy
                    </button>
                    <button 
                        wire:click="createNewChat"
                        @disabled(!$selectedUserId)
                        class="flex-1 px-4 py-2 bg-gray-900 hover:bg-gray-800 dark:bg-gray-800 dark:hover:bg-gray-700 text-white rounded-lg transition-colors font-medium disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        Tạo Chat
                    </button>
                </div>
            </div>
        </div>
    @endif

    <x-filament-actions::modals />
</div>

<script>
// Alpine.js component để quản lý Echo subscriptions
function chatBox() {
    return {
        currentChannel: null,
        
        init() {
            var self = this;

            // Ensure axios uses the page CSRF token for broadcasting auth
            try {
                if (window.axios) {
                    const tokenMeta = document.querySelector('meta[name="csrf-token"]');
                    if (tokenMeta && tokenMeta.content) {
                        window.axios.defaults.headers.common['X-CSRF-TOKEN'] = tokenMeta.content;
                    }
                }
            } catch (e) {
                console.warn('Could not set axios CSRF header', e);
            }

            // Lắng nghe sự kiện từ Livewire
            // Livewire v3 dispatch với named params trả về object {chatId: 10}
            this.$wire.on('chat-selected', function(event) {
                try {
                    // Validate payload shape
                    var chatId = null;
                    if (event && typeof event === 'object') {
                        chatId = event.chatId ?? event.chatId ?? null;
                    } else {
                        chatId = event;
                    }

                    // coerce to number when possible
                    chatId = chatId !== null ? Number(chatId) : null;

                    if (!chatId || Number.isNaN(chatId)) {
                        console.warn('chat-selected event received with invalid chatId:', event);
                        return;
                    }

                    console.log('Chat selected, ID:', chatId);
                    self.subscribeToChat(chatId);
                    self.scrollToBottom();
                } catch (err) {
                    console.error('Error handling chat-selected event', err, event);
                }
            });

            this.$wire.on('message-sent', function() {
                try { self.scrollToBottom(); } catch (e) { console.warn(e); }
            });

            // Subscribe vào chat ban đầu nếu có
            var initialChatId = @js($selectedChat?->id);
            if (initialChatId) {
                console.log('Initial chat ID:', initialChatId);
                this.subscribeToChat(initialChatId);
            }

            this.scrollToBottom();
        },
        
        subscribeToChat(chatId) {
            var self = this;
            
            // Unsubscribe khỏi channel cũ
            if (this.currentChannel) {
                try {
                    console.log('🔴 Leaving channel:', this.currentChannel);
                    if (window.Echo && typeof window.Echo.leave === 'function') {
                        window.Echo.leave(this.currentChannel);
                    }
                } catch (e) {
                    console.warn('Error leaving channel', e);
                }
                this.currentChannel = null;
            }
            
            // Tạo channel name
            var channelName = 'messages.' + chatId;
            
            console.log('🟢 Subscribing to channel:', 'private-' + channelName);
            
            // Guard: ensure Echo available and chatId is valid
            if (!chatId || !window.Echo) {
                console.warn('Cannot subscribe: invalid chatId or Echo not available', chatId, window.Echo);
                return;
            }

            // Subscribe vào channel mới
            var channel;
            try {
                channel = window.Echo.private(channelName);
            } catch (e) {
                console.error('Failed to create Echo private channel', e);
                return;
            }
            
            // Store current channel
            this.currentChannel = channelName;
            
            // IMPORTANT: Với broadcastAs(), cần dùng dấu chấm (.)
            // Listen for MessageCreated event
            channel.listen('.MessageCreated', function(event) {
                try {
                    console.log('🎉 Message received!', event);
                    if (self && self.$wire && typeof self.$wire.$refresh === 'function') {
                        self.$wire.$refresh();
                    }
                    setTimeout(function() {
                        self.scrollToBottom();
                    }, 200);
                } catch (e) {
                    console.error('Error handling MessageCreated event', e, event);
                }
            });
            
            // Error handler
            channel.error(function(error) {
                try {
                    console.error('❌ Channel error:', error);
                    // If auth error, log helpful hints
                    if (error && error.status === 403) {
                        console.error('Channel auth failed (403). Check /broadcasting/auth route, CSRF token, and broadcasting auth middleware.');
                    }
                } catch (e) {
                    console.error('Error in channel.error handler', e, error);
                }
            });
            
            // Subscription success callback
            channel.subscribed(function() {
                console.log('✅ Subscription confirmed for:', 'private-' + channelName);
            });
            
            console.log('📡 Listener registered for: .MessageCreated');
        },
        
        scrollToBottom() {
            setTimeout(function() {
                var chatMessages = document.getElementById('chat-messages');
                if (chatMessages) {
                    chatMessages.scrollTop = chatMessages.scrollHeight;
                }
            }, 100);
        }
    };
}
</script>
