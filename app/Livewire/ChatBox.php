<?php

namespace App\Livewire;

use App\Livewire\Concerns\HasChatContextMenu;
use App\Models\Chat;
use App\Models\Message;
use App\Models\User;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class ChatBox extends Component implements HasActions, HasForms
{
    use HasChatContextMenu;
    use InteractsWithActions;
    use InteractsWithForms;

    public $content = '';

    public $search = '';

    public $messageSearch = '';

    public $selectedChat = null;

    public $showNewChatModal = false;

    public $selectedUserId = null;

    public $userSearch = '';

    /**
     * Livewire Listeners
     * 
     * Không dùng echo listener ở đây vì mỗi chat có channel riêng
     * Thay vào đó, sẽ dùng JavaScript để lắng nghe dynamic channels
     * (xem phần @script trong blade file)
     */
    protected $listeners = [];

    public function mount()
    {
        $this->ensureGeneralChatExists();
        $this->selectedChat = Chat::where('type', 'general')->first();
    }

    protected function ensureGeneralChatExists(): void
    {
        Chat::firstOrCreate(
            ['type' => 'general'],
            ['type' => 'general']
        );
    }

    public function getChatsProperty()
    {
        // 1. Get General Chat
        $generalChat = Chat::where('type', 'general')->first();

        // 2. Get Private Chats (Where user is participant)
        // Exclude private_shop and private_transaction from chat list
        $privateChats = Auth::user()->chats()
            ->whereNotIn('type', ['private_shop', 'private_transaction'])
            ->with(['participants', 'messages' => function ($query) {
                $query->latest()->limit(1);
            }])
            ->latest('updated_at')
            ->get();

        // 3. Merge & Sort by last message time or update time
        $allChats = collect([$generalChat])->merge($privateChats)->unique('id');

        // 4. Search Filter
        if ($this->search) {
            $allChats = $allChats->filter(function ($chat) {
                return str_contains(strtolower($this->getChatName($chat)), strtolower($this->search));
            });
        }

        return $allChats;
    }

    public function getSelectedChatMessagesProperty()
    {
        if (! $this->selectedChat) {
            return collect();
        }

        // Lấy 50 tin nhắn mới nhất, sau đó sắp xếp theo thứ tự cũ -> mới
        $query = $this->selectedChat->messages()
            ->with('sender')
            ->latest()
            ->limit(50);

        // Filter by message search if provided
        if ($this->messageSearch) {
            $query->where('content', 'like', '%' . $this->messageSearch . '%');
        }

        return $query->get()
            ->sortBy('id')
            ->values(); // Reset keys để tránh lộn xộn thứ tự
    }

    public function selectChat($chatId)
    {
        $this->selectedChat = Chat::find($chatId);

        // Mark all unread messages in this chat as read
        $this->markMessagesAsRead();

        // Dispatch event với chatId để Alpine.js subscribe vào đúng channel
        $this->dispatch('chat-selected', chatId: $chatId);
    }

    protected function markMessagesAsRead(): void
    {
        if (! $this->selectedChat) {
            return;
        }

        // Mark all messages in this chat that were sent by others as read
        Message::where('chat_id', $this->selectedChat->id)
            ->where('sender_id', '!=', Auth::id())
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }

    public function getUnreadCountProperty($chatId = null)
    {
        if ($chatId) {
            return Message::where('chat_id', $chatId)
                ->where('sender_id', '!=', Auth::id())
                ->whereNull('read_at')
                ->count();
        }

        // Get total unread count across all chats
        return Message::whereHas('chat.participants', function ($query) {
            $query->where('user_id', Auth::id());
        })
            ->where('sender_id', '!=', Auth::id())
            ->whereNull('read_at')
            ->count();
    }

    public function getChatUnreadCount($chat)
    {
        return Message::where('chat_id', $chat->id)
            ->where('sender_id', '!=', Auth::id())
            ->whereNull('read_at')
            ->count();
    }

    public function getChatName($chat)
    {
        if ($chat->type === 'general') {
            return 'Chat Tổng';
        }

        // For private chats, find the OTHER participant
        $otherParticipant = $chat->participants->where('id', '!=', Auth::id())->first();

        return $otherParticipant ? $otherParticipant->name : 'Unknown';
    }

    public function getChatAvatar($chat)
    {
        if ($chat->type === 'general') {
            return null; // Return null to let view handle default icon
        }

        $otherParticipant = $chat->participants->where('id', '!=', Auth::id())->first();

        return $otherParticipant ? $otherParticipant->avatar_url : null;
    }

    public function sendMessage(): void
    {
        $this->validate([
            'content' => 'required|string|max:1000',
        ]);

        if (! $this->selectedChat) {
            return;
        }

        Message::create([
            'chat_id' => $this->selectedChat->id,
            'sender_id' => Auth::id(),
            'content' => $this->content,
        ]);

        $this->reset('content');
        $this->dispatch('message-sent');
    }

    public function openNewChatModal(): void
    {
        $this->showNewChatModal = true;
        $this->reset(['selectedUserId', 'userSearch']);
    }

    public function closeNewChatModal(): void
    {
        $this->showNewChatModal = false;
        $this->reset(['selectedUserId', 'userSearch']);
    }

    public function getAvailableUsersProperty(): Collection
    {
        $query = User::where('id', '!=', Auth::id());

        if ($this->userSearch) {
            $query->where(function ($q) {
                $q->where('username', 'like', '%'.$this->userSearch.'%')
                    ->orWhere('email', 'like', '%'.$this->userSearch.'%');
            });
        }

        return $query->limit(20)->get();
    }

    public function selectUser(int $userId): void
    {
        $this->selectedUserId = $userId;
    }

    public function createNewChat(): void
    {
        $this->validate([
            'selectedUserId' => 'required|exists:users,id',
        ]);

        // Check if chat already exists between these two users
        $existingChat = Chat::whereIn('type', ['private_middle', 'private_shop'])
            ->whereHas('participants', function ($query) {
                $query->where('user_id', Auth::id());
            })
            ->whereHas('participants', function ($query) {
                $query->where('user_id', $this->selectedUserId);
            })
            ->first();

        if ($existingChat) {
            // If chat exists, just select it
            $this->selectedChat = $existingChat;
            $this->closeNewChatModal();
            $this->dispatch('chat-selected', chatId: $existingChat->id);

            return;
        }

        // Create new private chat (using private_middle for general private chats)
        $chat = Chat::create(['type' => 'private_middle']);

        // Add both users as participants
        $chat->participants()->attach([Auth::id(), $this->selectedUserId]);

    // Select the new chat
    $this->selectedChat = $chat->load(['participants', 'messages']);
    $this->closeNewChatModal();
    // Notify frontend with the new chat id so Alpine subscribes to the correct channel
    $this->dispatch('chat-selected', chatId: $chat->id);
    }

    public function render()
    {
        return view('livewire.chat-box');
    }
}
