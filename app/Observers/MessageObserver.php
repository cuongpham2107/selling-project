<?php

namespace App\Observers;

use App\Events\MessageCreated as MessageCreatedEvent;
use App\Models\Message;

class MessageObserver
{
    /**
     * Handle the Message "created" event.
     */
    public function created(Message $message): void
    {
        // Dispatch event để broadcast qua Reverb
        MessageCreatedEvent::dispatch($message);
    }

    /**
     * Handle the Message "updated" event.
     */
    public function updated(Message $message): void
    {
        // Nếu tin nhắn bị xóa (deleted_at thay đổi), dispatch event để refresh UI cho các user khác
        if ($message->wasChanged('deleted_at')) {
            MessageCreatedEvent::dispatch($message);
        }
    }

    /**
     * Handle the Message "deleted" event.
     */
    public function deleted(Message $message): void
    {
        //
    }

    /**
     * Handle the Message "restored" event.
     */
    public function restored(Message $message): void
    {
        //
    }

    /**
     * Handle the Message "force deleted" event.
     */
    public function forceDeleted(Message $message): void
    {
        //
    }
}
