<?php
// app/Notifications/GeneralNotification.php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\BroadcastMessage;

class GeneralNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $data;

    /**
     * Create a new notification instance.
     */
    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via($notifiable): array
    {
        $channels = ['database', 'broadcast'];

        if (isset($this->data['email']) && $this->data['email']) {
            $channels[] = 'mail';
        }

        return $channels;
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail($notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject($this->data['title'] ?? 'Notification')
            ->greeting('Hello ' . $notifiable->first_name . '!')
            ->line($this->data['message']);

        if (isset($this->data['action_url']) && isset($this->data['action_text'])) {
            $mail->action($this->data['action_text'], $this->data['action_url']);
        }

        if (isset($this->data['image'])) {
            $mail->attach($this->data['image']);
        }

        return $mail;
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray($notifiable): array
    {
        return [
            'type' => $this->data['type'] ?? 'info',
            'title' => $this->data['title'] ?? 'Notification',
            'message' => $this->data['message'],
            'action_url' => $this->data['action_url'] ?? null,
            'action_text' => $this->data['action_text'] ?? null,
            'image' => $this->data['image'] ?? null,
            'sent_by' => $this->data['sent_by'] ?? 'System',
            'sent_at' => $this->data['sent_at'] ?? now(),
        ];
    }

    /**
     * Get the broadcastable representation of the notification.
     */
    public function toBroadcast($notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'type' => $this->data['type'] ?? 'info',
            'title' => $this->data['title'] ?? 'Notification',
            'message' => $this->data['message'],
            'action_url' => $this->data['action_url'] ?? null,
            'action_text' => $this->data['action_text'] ?? null,
            'time' => now()->diffForHumans(),
        ]);
    }
}