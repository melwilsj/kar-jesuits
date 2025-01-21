<?php

namespace App\Notifications;

use App\Models\ProvinceTransfer;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class ProvinceTransferRequestNotification extends Notification
{
    use Queueable;

    public function __construct(
        public ProvinceTransfer $transfer
    ) {}

    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Province Transfer Request')
            ->line("A transfer request has been initiated for {$this->transfer->user->name}")
            ->line("From: {$this->transfer->fromProvince->name}")
            ->line("To: {$this->transfer->toProvince->name}")
            ->action('View Request', url("/admin/transfers/{$this->transfer->id}"));
    }

    public function toArray($notifiable): array
    {
        return [
            'transfer_id' => $this->transfer->id,
            'user_id' => $this->transfer->user_id,
            'from_province' => $this->transfer->fromProvince->name,
            'to_province' => $this->transfer->toProvince->name,
        ];
    }
} 