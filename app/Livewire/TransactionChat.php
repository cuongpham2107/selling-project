<?php

namespace App\Livewire;

use App\Models\Message;
use App\Models\Chat;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class TransactionChat extends Component
{
    public $chatId;
    public $newMessage = '';

    public function mount($chatId)
    {
        $this->chatId = $chatId;
    }

    public function getMessagesProperty()
    {
        if (!$this->chatId) {
            return collect();
        }

        return Message::where('chat_id', $this->chatId)
            ->with('sender')
            ->orderBy('created_at', 'asc')
            ->get();
    }

    public function sendMessage()
    {
        if (empty(trim($this->newMessage)) || !$this->chatId) {
            return;
        }

        Message::create([
            'chat_id' => $this->chatId,
            'sender_id' => Auth::id(),
            'content' => $this->newMessage,
        ]);

        $this->newMessage = '';
        $this->dispatch('messageSent');
    }

    public function render()
    {
        return view('livewire.transaction-chat', [
            'messages' => $this->messages,
        ]);
    }
}
