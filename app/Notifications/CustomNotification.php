<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\DatabaseMessage;
use App\Models\User;

class CustomNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected string $message;
    protected string $type;
    protected array $data;
    protected ?User $sender;
    protected ?string $actionUrl;
    protected ?string $actionText;
    protected ?string $icon;
    protected ?string $image;
    protected array $channels;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        string $message, 
        string $type = 'info', 
        array $data = [],
        ?User $sender = null,
        ?string $actionUrl = null,
        ?string $actionText = null,
        ?string $icon = null,
        ?string $image = null,
        array $channels = ['database', 'broadcast']
    ) {
        $this->message = $message;
        $this->type = $type;
        $this->data = $data;
        $this->sender = $sender;
        $this->actionUrl = $actionUrl;
        $this->actionText = $actionText;
        $this->icon = $icon;
        $this->image = $image;
        $this->channels = $channels;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via($notifiable): array
    {
        $channels = [];

        foreach ($this->channels as $channel) {
            if ($notifiable->wantsNotification($channel, $this->type)) {
                $channels[] = $channel;
            }
        }

        return $channels;
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail($notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject($this->getSubject())
            ->greeting($this->getGreeting($notifiable))
            ->line($this->message);

        if ($this->actionUrl && $this->actionText) {
            $mail->action($this->actionText, $this->actionUrl);
        }

        if ($this->image) {
            $mail->attach($this->image);
        }

        return $mail->line('Thank you for using our application!');
    }

    /**
     * Get the database representation of the notification.
     */
    public function toDatabase($notifiable): array
    {
        return [
            'message' => $this->message,
            'type' => $this->type,
            'data' => $this->data,
            'sender_id' => $this->sender?->id,
            'sender_name' => $this->sender?->full_name,
            'sender_avatar' => $this->sender?->profile_photo_url,
            'action_url' => $this->actionUrl,
            'action_text' => $this->actionText,
            'icon' => $this->icon ?? $this->getDefaultIcon(),
            'image' => $this->image,
            'read_at' => null,
        ];
    }

    /**
     * Get the broadcast representation of the notification.
     */
    public function toBroadcast($notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'id' => $this->id,
            'message' => $this->message,
            'type' => $this->type,
            'data' => $this->data,
            'sender' => $this->sender ? [
                'id' => $this->sender->id,
                'name' => $this->sender->full_name,
                'avatar' => $this->sender->profile_photo_url,
            ] : null,
            'action_url' => $this->actionUrl,
            'action_text' => $this->actionText,
            'icon' => $this->icon ?? $this->getDefaultIcon(),
            'created_at' => now()->toIso8601String(),
        ]);
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray($notifiable): array
    {
        return [
            'message' => $this->message,
            'type' => $this->type,
            'data' => $this->data,
            'sender' => $this->sender,
            'action_url' => $this->actionUrl,
            'action_text' => $this->actionText,
        ];
    }

    /**
     * Get notification subject
     */
    protected function getSubject(): string
    {
        return match ($this->type) {
            'alert' => '⚠️ Alert Notification',
            'success' => '✅ Success Notification',
            'warning' => '⚠️ Warning Notification',
            'error' => '❌ Error Notification',
            'admin' => '👔 Admin Notification',
            'system' => '🔧 System Notification',
            default => '📬 New Notification',
        };
    }

    /**
     * Get notification greeting
     */
    protected function getGreeting($notifiable): string
    {
        return "Hello {$notifiable->first_name}!";
    }

    /**
     * Get default icon based on type
     */
    protected function getDefaultIcon(): string
    {
        return match ($this->type) {
            'alert' => '⚠️',
            'success' => '✅',
            'warning' => '⚠️',
            'error' => '❌',
            'admin' => '👔',
            'system' => '🔧',
            default => '📬',
        };
    }
}