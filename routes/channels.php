<?php

use App\Models\Chat;
use Illuminate\Support\Facades\Broadcast;

/**
 * Channel cho user cá nhân
 * Chỉ user đó mới được lắng nghe channel của chính mình
 */
Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

/**
 * Channel cho messages broadcast
 * Format: messages.{chatId}
 * 
 * Logic phân quyền:
 * - Chat type = 'general': TẤT CẢ user đều có quyền lắng nghe (public chat)
 * - Chat type = 'private_middle', 'private_shop', 'private_transaction': 
 *   CHỈ có 2 người tham gia trong chat mới được lắng nghe (private chat)
 * 
 * @param \App\Models\User $user - User đang cố gắng subscribe channel
 * @param int $chatId - ID của chat cần kiểm tra quyền
 * @return bool - true nếu user có quyền lắng nghe, false nếu không
 */
Broadcast::channel('messages.{chatId}', function ($user, $chatId) {
    // Cast chatId sang integer để đảm bảo đúng type
    $chatId = (int) $chatId;
    
    // Log để debug
    \Log::info('Broadcasting auth check', [
        'user_id' => $user->id,
        'chat_id' => $chatId,
    ]);
    
    // Tìm chat theo ID
    $chat = Chat::find($chatId);

    // Nếu chat không tồn tại, từ chối quyền truy cập
    if (! $chat) {
        \Log::warning('Chat not found', ['chat_id' => $chatId]);
        return false;
    }

    // Nếu là general chat (Chat Tổng), ai cũng có thể lắng nghe
    // Đây là public chat room cho tất cả users
    if ($chat->type === 'general') {
        \Log::info('General chat - authorized', ['chat_id' => $chatId]);
        return true;
    }

    // Với các loại chat private khác (private_middle, private_shop, private_transaction)
    // CHỈ có participants (2 người trong đoạn chat) mới được lắng nghe
    // Kiểm tra xem user hiện tại có phải là một trong 2 người tham gia không
    $isParticipant = $chat->participants()->where('user_id', $user->id)->exists();
    
    \Log::info('Private chat auth check', [
        'chat_id' => $chatId,
        'user_id' => $user->id,
        'is_participant' => $isParticipant,
        'chat_type' => $chat->type,
    ]);
    
    return $isParticipant;
});
