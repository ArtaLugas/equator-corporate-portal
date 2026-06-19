<?php

namespace App\Notifications;

use App\Models\Message;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

class NewContactMessage extends Notification
{
    public function __construct(public Message $message) {}

    /**
     * Delivery channels.
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Stored payload (rendered generically by the topbar / notifications page).
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'message',
            'icon' => 'mail',
            'title' => 'New contact message',
            'body' => $this->message->name.' — '.Str::limit($this->message->subject ?: 'No subject', 50),
            'url' => route('admin.messages.show', $this->message->id),
            'message_id' => $this->message->id,
        ];
    }
}
