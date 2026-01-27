<?php

namespace App\Events;

use App\Models\Message;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageCreated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Message vừa được tạo
     * 
     * @var Message
     */
    public Message $message;

    /**
     * Tạo instance mới của event
     * 
     * @param Message $message - Tin nhắn vừa được tạo
     */
    public function __construct(Message $message)
    {
        $this->message = $message;
    }

    /**
     * Xác định channel nào event sẽ broadcast đến
     * 
     * Logic:
     * - Broadcast đến channel: messages.{chatId}
     * - VD: messages.1, messages.5, messages.10
     * - Mỗi chat có một channel riêng
     * - Authorization được handle ở routes/channels.php
     * 
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            // Broadcast đến channel private của chat cụ thể
            // Format: messages.{chatId}
            new PrivateChannel('messages.'.$this->message->chat_id),
        ];
    }

    /**
     * Tên event khi broadcast
     * Frontend sẽ lắng nghe: "MessageCreated"
     * 
     * @return string
     */
    public function broadcastAs(): string
    {
        return 'MessageCreated';
    }

    /**
     * Data được gửi kèm khi broadcast
     * 
     * Trả về:
     * - message_id: ID của tin nhắn mới
     * - chat_id: ID của chat chứa tin nhắn
     * 
     * Frontend có thể dùng data này để refresh hoặc thêm message mới vào UI
     * 
     * @return array
     */
    public function broadcastWith(): array
    {
        return [
            'message_id' => $this->message->id,
            'chat_id' => $this->message->chat_id,
        ];
    }
}
