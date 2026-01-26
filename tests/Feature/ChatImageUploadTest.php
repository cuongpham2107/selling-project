<?php

namespace Tests\Feature;

use App\Models\Chat;
use App\Models\Message;
use App\Models\User;
use App\Livewire\TransactionChat;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class ChatImageUploadTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_upload_image_in_chat()
    {
        Storage::fake('public');
        $user = User::factory()->create();
        $chat = Chat::create(['type' => 'private_transaction']);

        Livewire::actingAs($user)
            ->test(TransactionChat::class, ['chatId' => $chat->id])
            ->set('newMessage', 'Hello with image')
            ->set('image', UploadedFile::fake()->image('test.jpg'))
            ->call('sendMessage');

        $this->assertEquals(1, Message::count());
        $message = Message::first();
        $this->assertNotNull($message->image_url);
        Storage::disk('public')->assertExists('chat-images/' . basename($message->image_url));
    }

    public function test_image_upload_limit_per_day()
    {
        Storage::fake('public');
        $user = User::factory()->create();
        $chat = Chat::create(['type' => 'private_transaction']);

        $test = Livewire::actingAs($user)
            ->test(TransactionChat::class, ['chatId' => $chat->id]);

        // Send 3 images
        for ($i = 0; $i < 3; $i++) {
            $test->set('image', UploadedFile::fake()->image("test{$i}.jpg"))
                ->call('sendMessage')
                ->assertHasNoErrors('image');
        }

        $this->assertEquals(3, Message::whereNotNull('image_url')->count());

        // Send 4th image
        $test->set('image', UploadedFile::fake()->image('test4.jpg'))
            ->call('sendMessage')
            ->assertHasErrors(['image' => 'Bạn đã đạt giới hạn gửi 3 ảnh mỗi ngày cho giao dịch này.']);

        $this->assertEquals(3, Message::whereNotNull('image_url')->count());
    }

    public function test_image_size_limit()
    {
        Storage::fake('public');
        $user = User::factory()->create();
        $chat = Chat::create(['type' => 'private_transaction']);

        Livewire::actingAs($user)
            ->test(TransactionChat::class, ['chatId' => $chat->id])
            ->set('image', UploadedFile::fake()->image('large.jpg')->size(2048)) // 2MB
            ->call('sendMessage')
            ->assertHasErrors(['image']);
    }
}
