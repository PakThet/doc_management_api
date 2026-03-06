<?php

namespace App\Notifications;

use App\Models\Document;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class DocumentNotification extends Notification
{
    use Queueable;

    protected Document $document;
    protected string $action;
    protected ?User $actor;
    protected array $changes;

    public function __construct(Document $document, string $action, ?User $actor = null, array $changes = [])
    {
        $this->document = $document;
        $this->action = $action;
        $this->actor = $actor;
        $this->changes = $changes;
    }

    public function via($notifiable): array
    {
        return ['database', 'broadcast'];
    }

    public function toDatabase($notifiable): array
    {
        $messages = [
            'created' => 'created a new document',
            'updated' => 'updated a document',
            'deleted' => 'deleted a document',
            'published' => 'published a document',
            'archived' => 'archived a document',
            'expired' => 'document has expired',
            'verified' => 'verified a document',
            'downloaded' => 'downloaded a document',
        ];

        $message = $this->actor 
            ? "{$this->actor->full_name} {$messages[$this->action]}: {$this->document->title}"
            : "Document {$messages[$this->action]}: {$this->document->title}";

        return [
            'message' => $message,
            'type' => 'document',
            'action' => $this->action,
            'document' => [
                'id' => $this->document->id,
                'title' => $this->document->title,
                'code' => $this->document->document_code,
                'status' => $this->document->status,
            ],
            'actor' => $this->actor ? [
                'id' => $this->actor->id,
                'name' => $this->actor->full_name,
                'avatar' => $this->actor->profile_photo_url,
            ] : null,
            'changes' => $this->changes,
            'action_url' => route('documents.show', $this->document),
            'action_text' => 'View Document',
            'icon' => '📄',
        ];
    }

    public function toBroadcast($notifiable): array
    {
        return [
            'data' => $this->toDatabase($notifiable),
            'created_at' => now()->toIso8601String(),
        ];
    }
}