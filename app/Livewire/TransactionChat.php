<?php

namespace App\Livewire;

use App\Models\Message;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithFileUploads;

class TransactionChat extends Component
{
    use WithFileUploads;

    public $chatId;

    public $newMessage = '';

    public $image;

    public function mount($chatId)
    {
        $this->chatId = $chatId;
    }

    public function getMessagesProperty()
    {
        if (! $this->chatId) {
            return collect();
        }

        return Message::query()->where('chat_id', $this->chatId)
            ->with('sender')
            ->orderBy('created_at', 'asc')
            ->get();
    }

    public function sendMessage()
    {
        // Validate image if present
        if ($this->image) {
            $this->validate([
                'image' => 'image|max:1024', // Max 1MB
            ]);
        }

        if (empty(trim($this->newMessage)) && ! $this->image) {
            return;
        }

        if (! $this->chatId) {
            return;
        }

        $imageUrl = null;
        if ($this->image) {
            $imageUrl = $this->image->store('chat-images', 'public');
        }

        Message::create([
            'chat_id' => $this->chatId,
            'sender_id' => Auth::id(),
            'content' => $this->newMessage,
            'image_url' => $imageUrl,
        ]);

        $this->reset(['newMessage', 'image']);
        $this->dispatch('messageSent');
    }

    public function render()
    {
        return view('livewire.transaction-chat', [
            'messages' => $this->messages,
        ]);
    }
}
