# 🚀 Laravel Reverb Real-time Chat - Tài Liệu Tổng Hợp

## 📋 Mục Lục
1. [Tổng Quan](#tổng-quan)
2. [Kiến Trúc Hệ Thống](#kiến-trúc-hệ-thống)
3. [Cài Đặt & Cấu Hình](#cài-đặt--cấu-hình)
4. [Flow Hoạt Động](#flow-hoạt-động)
5. [Các Thành Phần Chính](#các-thành-phần-chính)
6. [Cách Sử Dụng](#cách-sử-dụng)
7. [Debug & Troubleshooting](#debug--troubleshooting)

---

## 🎯 Tổng Quan

Hệ thống chat real-time được xây dựng sử dụng:
- **Laravel Reverb** - WebSocket server của Laravel
- **Laravel Echo** - JavaScript client để lắng nghe events
- **Livewire 3** - Full-stack framework cho reactive components
- **Alpine.js** - Quản lý Echo subscriptions tách biệt khỏi Livewire lifecycle
- **Filament** - Admin panel framework

### ✨ Tính Năng
- ✅ Real-time messaging (không cần reload trang)
- ✅ General chat (public cho tất cả users)
- ✅ Private chat (1-1 messaging)
- ✅ Transaction chat (chat riêng cho giao dịch)
- ✅ Shop chat (chat riêng cho shop)
- ✅ Read receipts (đánh dấu đã đọc)
- ✅ Authorization (phân quyền ai được lắng nghe channel nào)
- ✅ Auto scroll to bottom
- ✅ Unread message counter

---

## 🏗️ Kiến Trúc Hệ Thống

```
┌─────────────────┐
│   User A        │
│   (Browser)     │
└────────┬────────┘
         │ 1. Gửi tin nhắn
         ↓
┌─────────────────────────────────┐
│   Laravel Backend               │
│                                 │
│  ChatBox.php (Livewire)        │
│    ↓                            │
│  Message::create()              │
│    ↓                            │
│  MessageObserver::created()     │
│    ↓                            │
│  event(MessageCreated)          │
└────────┬────────────────────────┘
         │ 2. Broadcast event
         ↓
┌─────────────────────────────────┐
│   Laravel Reverb Server         │
│   (WebSocket - Port 8080)       │
│                                 │
│   Channel: private-messages.7   │
└────────┬────────────────────────┘
         │ 3. Push to subscribers
         ↓
┌─────────────────┐
│   User B        │
│   (Browser)     │
│                 │
│  Laravel Echo   │
│    ↓            │
│  Alpine.js      │
│    ↓            │
│  $wire.$refresh()│
│    ↓            │
│  Livewire Update│
└─────────────────┘
```

---

## ⚙️ Cài Đặt & Cấu Hình

### 1. Environment Variables (.env)
```env
BROADCAST_CONNECTION=reverb

REVERB_APP_ID=ytcn6i0nmyvyxldbp0r5
REVERB_APP_KEY=ytcn6i0nmyvyxldbp0r5
REVERB_APP_SECRET=nqpb6d3nkk0h4kwg97fa
REVERB_HOST=localhost
REVERB_PORT=8080
REVERB_SCHEME=http

VITE_REVERB_APP_KEY="${REVERB_APP_KEY}"
VITE_REVERB_HOST="${REVERB_HOST}"
VITE_REVERB_PORT="${REVERB_PORT}"
VITE_REVERB_SCHEME="${REVERB_SCHEME}"
```

### 2. Config Files

#### config/broadcasting.php
```php
'reverb' => [
    'driver' => 'reverb',
    'key' => env('REVERB_APP_KEY'),
    'secret' => env('REVERB_APP_SECRET'),
    'app_id' => env('REVERB_APP_ID'),
    'options' => [
        'host' => env('REVERB_HOST'),
        'port' => env('REVERB_PORT'),
        'scheme' => env('REVERB_SCHEME'),
        'useTLS' => env('REVERB_SCHEME') === 'https',
    ],
],
```

#### config/filament.php
```php
'broadcasting' => [
    'echo' => [
        'broadcaster' => 'reverb',
        'key' => env('VITE_REVERB_APP_KEY'),
        'cluster' => env('VITE_PUSHER_APP_CLUSTER', 'mt1'),
        'wsHost' => env('VITE_REVERB_HOST', '127.0.0.1'),
        'wsPort' => env('VITE_REVERB_PORT', 8080),
        'wssPort' => env('VITE_REVERB_PORT', 8080),
        'forceTLS' => false,
        'enabledTransports' => ['ws', 'wss'],
        'disableStats' => true,
    ],
],
```

#### resources/js/app.js
```javascript
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

window.Echo = new Echo({
    broadcaster: 'reverb',
    key: import.meta.env.VITE_REVERB_APP_KEY,
    wsHost: import.meta.env.VITE_REVERB_HOST,
    wsPort: import.meta.env.VITE_REVERB_PORT,
    wssPort: import.meta.env.VITE_REVERB_PORT,
    forceTLS: (import.meta.env.VITE_REVERB_SCHEME ?? 'https') === 'https',
    enabledTransports: ['ws', 'wss'],
    disableStats: true,
});
```

### 3. Database Migrations

#### Chats Table
```php
Schema::create('chats', function (Blueprint $table) {
    $table->id();
    $table->enum('type', ['general', 'private_middle', 'private_shop', 'private_transaction']);
    $table->timestamps();
});
```

#### Messages Table
```php
Schema::create('messages', function (Blueprint $table) {
    $table->id();
    $table->foreignId('chat_id')->constrained()->onDelete('cascade');
    $table->foreignId('sender_id')->constrained('users')->onDelete('cascade');
    $table->text('content')->nullable();
    $table->string('image_url')->nullable();
    $table->timestamp('read_at')->nullable();
    $table->timestamps();
    
    $table->index(['chat_id', 'created_at']);
    $table->index(['chat_id', 'read_at']);
});
```

#### Chat Participants Table
```php
Schema::create('chat_participants', function (Blueprint $table) {
    $table->foreignId('chat_id')->constrained()->onDelete('cascade');
    $table->foreignId('user_id')->constrained()->onDelete('cascade');
    $table->primary(['chat_id', 'user_id']);
});
```

---

## 🔄 Flow Hoạt Động

### 1️⃣ User Gửi Tin Nhắn

```php
// ChatBox.php
public function sendMessage(): void
{
    $this->validate([
        'content' => 'required|string|max:1000',
    ]);

    // Tạo message mới
    Message::create([
        'chat_id' => $this->selectedChat->id,
        'sender_id' => Auth::id(),
        'content' => $this->content,
    ]);

    $this->reset('content');
    $this->dispatch('message-sent');
}
```

### 2️⃣ Observer Tự Động Dispatch Event

```php
// MessageObserver.php
public function created(Message $message): void
{
    // Tự động broadcast event khi message được tạo
    event(new MessageCreated($message->id, $message->chat_id));
}
```

### 3️⃣ Event Broadcast Đến Channel

```php
// MessageCreated.php
class MessageCreated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public int $messageId,
        public int $chatId
    ) {}

    // Broadcast đến channel private-messages.{chatId}
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('messages.' . $this->chatId),
        ];
    }

    public function broadcastAs(): string
    {
        return 'MessageCreated';
    }

    // Data được gửi đến client
    public function broadcastWith(): array
    {
        return [
            'message_id' => $this->messageId,
            'chat_id' => $this->chatId,
        ];
    }
}
```

### 4️⃣ Authorization Check

```php
// routes/channels.php
Broadcast::channel('messages.{chatId}', function ($user, $chatId) {
    $chatId = (int) $chatId;
    $chat = Chat::find($chatId);

    if (!$chat) {
        return false;
    }

    // GENERAL CHAT: Ai cũng có thể lắng nghe
    if ($chat->type === 'general') {
        return true;
    }

    // PRIVATE CHAT: Chỉ 2 người trong chat mới được lắng nghe
    return $chat->participants()->where('user_id', $user->id)->exists();
});
```

### 5️⃣ Client Nhận Event & Update UI

```javascript
// Alpine.js component trong chat-box.blade.php
subscribeToChat(chatId) {
    var self = this;
    var channelName = 'messages.' + chatId;
    
    window.Echo.private(channelName)
        .listen('MessageCreated', function(event) {
            console.log('✅ New message received!', event);
            
            // Refresh Livewire component
            self.$wire.$refresh();
            
            // Scroll xuống cuối
            self.scrollToBottom();
        });
}
```

---

## 🧩 Các Thành Phần Chính

### 1. Event: MessageCreated

**File:** `app/Events/MessageCreated.php`

**Chức năng:** 
- Broadcast tin nhắn mới đến subscribers
- Được dispatch tự động bởi MessageObserver khi message được tạo

**Channel:** `private-messages.{chatId}`

**Data:**
```json
{
    "message_id": 23,
    "chat_id": 7
}
```

---

### 2. Observer: MessageObserver

**File:** `app/Observers/MessageObserver.php`

**Chức năng:**
- Tự động dispatch event `MessageCreated` khi message được tạo
- Không cần manually dispatch trong controller

**Đăng ký trong:** `app/Providers/AppServiceProvider.php`
```php
public function boot(): void
{
    Message::observe(MessageObserver::class);
}
```

---

### 3. Livewire Component: ChatBox

**File:** `app/Livewire/ChatBox.php`

**Methods chính:**
- `mount()` - Khởi tạo, tạo general chat nếu chưa có
- `selectChat($chatId)` - Chọn chat, mark messages as read
- `sendMessage()` - Gửi tin nhắn mới
- `getChatsProperty()` - Lấy danh sách chats
- `getChatName()` - Lấy tên chat (General hoặc tên user)
- `getChatUnreadCount()` - Đếm số tin nhắn chưa đọc

**Computed Properties:**
- `$this->chats` - Danh sách tất cả chats
- `$this->selectedChatMessages` - 50 tin nhắn mới nhất của chat đang chọn
- `$this->unreadCount` - Tổng số tin nhắn chưa đọc

---

### 4. Blade View với Alpine.js

**File:** `resources/views/livewire/chat-box.blade.php`

**Alpine.js Component:**
```javascript
function chatBox() {
    return {
        currentChannel: null,
        
        init() {
            // Lắng nghe events từ Livewire
            this.$wire.on('chat-selected', function(event) {
                var chatId = event.chatId || event;
                self.subscribeToChat(chatId);
            });
        },
        
        subscribeToChat(chatId) {
            // Unsubscribe channel cũ
            if (this.currentChannel) {
                window.Echo.leave(this.currentChannel);
            }
            
            // Subscribe channel mới
            var channelName = 'messages.' + chatId;
            this.currentChannel = channelName;
            
            window.Echo.private(channelName)
                .listen('MessageCreated', function(event) {
                    self.$wire.$refresh();
                    self.scrollToBottom();
                });
        }
    };
}
```

**Tại sao dùng Alpine.js thay vì Livewire @script?**
- Livewire component re-render → Event listeners bị mất
- Alpine.js component persistent → Listeners luôn hoạt động
- Tách biệt Echo lifecycle khỏi Livewire lifecycle

---

### 5. Broadcasting Channels

**File:** `routes/channels.php`

**Logic phân quyền:**

```php
// Chat type = 'general' → Public cho tất cả
if ($chat->type === 'general') {
    return true;
}

// Chat type = 'private_*' → Chỉ participants
return $chat->participants()
    ->where('user_id', $user->id)
    ->exists();
```

**Channel format:** `private-messages.{chatId}`

**Ví dụ:**
- `private-messages.7` - General chat (ID 7)
- `private-messages.10` - Private chat giữa User 1 và User 4

---

## 📖 Cách Sử Dụng

### Chạy Reverb Server

```bash
php artisan reverb:start

# Hoặc với debug mode
php artisan reverb:start --debug
```

### Chạy Development Server

```bash
# Terminal 1: Laravel dev server
php artisan serve

# Terminal 2: Reverb WebSocket server
php artisan reverb:start

# Terminal 3: Vite (nếu cần hot reload)
npm run dev
```

### Build Assets

```bash
npm run build
```

### Tạo Chat Mới

**General Chat** (Tự động tạo khi mount):
```php
Chat::firstOrCreate(
    ['type' => 'general'],
    ['type' => 'general']
);
```

**Private Chat:**
```php
// Tạo chat
$chat = Chat::create(['type' => 'private_middle']);

// Thêm participants
$chat->participants()->attach([
    Auth::id(),      // User hiện tại
    $otherUserId,    // User khác
]);
```

### Gửi Tin Nhắn

```php
Message::create([
    'chat_id' => $chatId,
    'sender_id' => Auth::id(),
    'content' => 'Hello!',
]);

// Event tự động được dispatch bởi Observer
```

---

## 🐛 Debug & Troubleshooting

### 1. Kiểm Tra Echo Initialization

**Browser Console:**
```javascript
window.Echo
// Phải trả về Echo instance, không phải undefined
```

### 2. Kiểm Tra Subscription

**Browser Console Log:**
```
Chat selected, ID: 7
Leaving channel: messages.3
Subscribing to channel: private-messages.7
✅ Successfully subscribed to: private-messages.7
```

**❌ SAI:**
```
Subscribing to channel: private-messages.[object Object]
```
→ Fix: Đảm bảo extract chatId đúng: `event.chatId || event`

### 3. Kiểm Tra Broadcasting

**Reverb Server Log:**
```
Broadcasting To: private-messages.7
{
    "event": "MessageCreated",
    "data": {
        "message_id": 23,
        "chat_id": 7
    },
    "channel": "private-messages.7"
}
```

### 4. Kiểm Tra Authorization

**Laravel Log:** `storage/logs/laravel.log`
```
Broadcasting auth check: {user_id: 1, chat_id: 7}
General chat - authorized: {chat_id: 7}
```

**403 Forbidden:**
```
Private chat auth check: {
    chat_id: 10,
    user_id: 1,
    is_participant: false  ← Vấn đề ở đây
}
```

**Fix:**
```bash
php artisan tinker
>>> $chat = Chat::find(10);
>>> $chat->participants()->attach(1); // Add user vào participants
```

### 5. Kiểm Tra Event Listener

**Browser Console:**
```javascript
// Log khi nhận message
✅ New message received! {message_id: 23, chat_id: 7}
```

**Nếu không thấy log:**
- Check `listen('MessageCreated')` có đúng event name không
- Check `broadcastAs()` trong Event class
- Hard refresh browser (Cmd + Shift + R)

### 6. Common Issues

#### Issue: `[object Object]` trong channel name
**Fix:** Extract chatId từ event object
```javascript
var chatId = event.chatId || event;
```

#### Issue: 403 Forbidden khi subscribe
**Check:**
```bash
php artisan tinker
>>> $chat = Chat::find(10);
>>> $chat->participants()->pluck('id');
>>> // Phải có user ID của người đang login
```

#### Issue: Message không realtime
**Check:**
1. Reverb server có đang chạy không?
2. Browser console có log subscribe thành công không?
3. Reverb log có show broadcast event không?
4. Echo listener có được set up đúng không?

#### Issue: Duplicate subscriptions
**Fix:** Unsubscribe channel cũ trước khi subscribe mới
```javascript
if (this.currentChannel) {
    window.Echo.leave(this.currentChannel);
}
```

---

## 🎓 Best Practices

### 1. Luôn Unsubscribe Khi Chuyển Chat
```javascript
if (this.currentChannel) {
    window.Echo.leave(this.currentChannel);
}
```

### 2. Dùng Alpine.js Cho Echo Management
- Tách biệt khỏi Livewire lifecycle
- Persistent listeners
- Better performance

### 3. Log Để Debug
```javascript
console.log('Subscribing to:', channelName);
console.log('New message:', event);
```

### 4. Type Casting Trong Authorization
```php
$chatId = (int) $chatId; // Đảm bảo đúng type
```

### 5. Error Handling
```javascript
window.Echo.private(channelName)
    .listen('MessageCreated', function(event) {
        // Success handler
    })
    .error(function(error) {
        console.error('Echo error:', error);
    });
```

---

## 📝 Checklist Triển Khai

### Development
- [x] Cài đặt Laravel Reverb
- [x] Config broadcasting với reverb
- [x] Tạo Event MessageCreated
- [x] Tạo Observer để auto-dispatch event
- [x] Setup channels authorization
- [x] Config Laravel Echo trong app.js
- [x] Tích hợp Alpine.js cho Echo management
- [x] Xóa `wire:poll` để tránh reload
- [x] Test real-time messaging

### Production
- [ ] Đổi `REVERB_SCHEME=https` trong .env
- [ ] Setup SSL certificate cho WebSocket
- [ ] Configure firewall cho port 8080
- [ ] Setup process manager (Supervisor) cho Reverb
- [ ] Monitor Reverb logs
- [ ] Setup rate limiting
- [ ] Optimize Echo reconnection strategy

---

## 🚀 Kết Luận

Hệ thống chat real-time đã được cấu hình hoàn chỉnh với:
- ✅ Laravel Reverb WebSocket server
- ✅ Broadcasting events tự động
- ✅ Authorization phân quyền theo chat type
- ✅ Alpine.js quản lý Echo subscriptions
- ✅ Real-time updates không cần reload

**Next Steps:**
- Thêm typing indicator
- Thêm online/offline status
- Thêm message reactions
- Thêm file upload
- Optimize performance với pagination

1