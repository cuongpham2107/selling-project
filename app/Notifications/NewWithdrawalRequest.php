<?php

namespace App\Notifications;

use App\Models\Withdrawal;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewWithdrawalRequest extends Notification implements ShouldQueue
{
    use Queueable;

    public $withdrawal;

    /**
     * Create a new notification instance.
     */
    public function __construct(Withdrawal $withdrawal)
    {
        $this->withdrawal = $withdrawal;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $url = url('/admin/withdrawals');

        return (new MailMessage)
            ->subject('🔔 Yêu cầu rút tiền mới #'.$this->withdrawal->id)
            ->greeting('Xin chào Admin!')
            ->line('Có một yêu cầu rút tiền mới cần được xử lý.')
            ->line('**Người dùng:** '.$this->withdrawal->user->username)
            ->line('**Số tiền:** '.number_format($this->withdrawal->amount, 0, ',', '.').' VNĐ')
            ->line('**Phương thức:** '.$this->withdrawal->type)
            ->line('**Trạng thái:** Chờ duyệt')
            ->action('Xem chi tiết', $url)
            ->line('Vui lòng xử lý yêu cầu này sớm nhất có thể.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'withdrawal_id' => $this->withdrawal->id,
            'user_id' => $this->withdrawal->user_id,
            'username' => $this->withdrawal->user->username,
            'amount' => $this->withdrawal->amount,
            'type' => $this->withdrawal->type,
            'status' => $this->withdrawal->status,
        ];
    }
}
