<?php

namespace Database\Seeders;

use App\Models\Message;
use Database\Seeders\Concerns\LoadsSeedData;
use Illuminate\Database\Seeder;

class MessageSeeder extends Seeder
{
    use LoadsSeedData;

    private const VALID_STATUS = ['unread', 'read', 'replied', 'archived', 'spam'];

    public function run(): void
    {
        foreach ($this->loadData('messages') as $row) {

            // Map status: spam flag wins, otherwise keep the legacy status.
            $status = ! empty($row['is_spam'])
                ? Message::STATUS_SPAM
                : (in_array($row['status'] ?? '', self::VALID_STATUS, true) ? $row['status'] : Message::STATUS_READ);

            $message = Message::updateOrCreate(
                ['email' => $row['email'], 'created_at' => $row['created_at']],
                [
                    'name' => $this->clip($row['name']),
                    'subject' => $this->clip($row['subject'] ?? null),
                    'message' => $this->nullable($row['message'] ?? null),
                    'ip_address' => $this->nullable($row['ip_address'] ?? null),
                    'status' => $status,
                    'replied_at' => $this->nullable($row['replied_at'] ?? null),
                    'updated_at' => $row['updated_at'] ?? now(),
                ]
            );

            // Recreate the legacy reply (single reply text) as a MessageReply.
            if (! blank($row['reply_message'] ?? null) && $message->replies()->doesntExist()) {
                $message->replies()->create([
                    'admin_id' => null,
                    'subject' => 'Re: '.($row['subject'] ?? ''),
                    'reply_message' => $row['reply_message'],
                    'sent_at' => $this->nullable($row['replied_at'] ?? null) ?? $message->updated_at,
                ]);
            }
        }
    }
}
