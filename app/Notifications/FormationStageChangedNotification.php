<?php

namespace App\Notifications;

use App\Models\JesuitFormation;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class FormationStageChangedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public JesuitFormation $formation
    ) {}

    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Formation Stage Update')
            ->line("{$this->formation->user->name}'s formation stage has been updated")
            ->line("New Stage: {$this->formation->stage->name}")
            ->line("Start Date: {$this->formation->start_date->format('d M Y')}")
            ->action('View Details', url("/admin/formation/{$this->formation->id}"));
    }

    public function toArray($notifiable): array
    {
        return [
            'formation_id' => $this->formation->id,
            'user_id' => $this->formation->user_id,
            'stage_name' => $this->formation->stage->name,
            'start_date' => $this->formation->start_date
        ];
    }
} 