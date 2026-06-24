<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Message extends Model
{
    use HasFactory, SoftDeletes;

    /*
    |--------------------------------------------------------------------------
    | Status Constants
    |--------------------------------------------------------------------------
    */

    public const STATUS_UNREAD = 'unread';

    public const STATUS_READ = 'read';

    public const STATUS_REPLIED = 'replied';

    public const STATUS_ARCHIVED = 'archived';

    public const STATUS_SPAM = 'spam';

    public const STATUSES = [
        self::STATUS_UNREAD,
        self::STATUS_READ,
        self::STATUS_REPLIED,
        self::STATUS_ARCHIVED,
        self::STATUS_SPAM,
    ];

    protected $fillable = [
        'reference',
        'name',
        'email',
        'phone',
        'company',
        'subject',
        'message',
        'ip_address',
        'user_agent',
        // Lead-source attribution (auto-captured).
        'landing_page',
        'referrer',
        'locale',
        'utm_source',
        'utm_medium',
        'utm_campaign',
        'utm_content',
        'utm_term',
        'gclid',
        'fbclid',
        'status',
        'replied_at',
        'archived_at',
    ];

    /*
    |--------------------------------------------------------------------------
    | Reference Number (e.g. EQ-20260715-000034) — unique, derived from the id.
    |--------------------------------------------------------------------------
    */

    protected static function booted(): void
    {
        static::created(function (Message $message) {
            if (blank($message->reference)) {
                $message->reference = sprintf(
                    'EQ-%s-%06d',
                    ($message->created_at ?? now())->format('Ymd'),
                    $message->id
                );
                $message->saveQuietly();
            }
        });
    }

    protected $casts = [
        'replied_at' => 'datetime',
        'archived_at' => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function replies(): HasMany
    {
        return $this->hasMany(MessageReply::class)->latest('sent_at');
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeStatus(Builder $query, ?string $status): Builder
    {
        return $query->when(
            $status && in_array($status, self::STATUSES, true),
            fn ($q) => $q->where('status', $status)
        );
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        return $query->when($term, function ($q) use ($term) {
            $term = trim($term);
            $q->where(function ($inner) use ($term) {
                $inner->where('name', 'like', "%{$term}%")
                    ->orWhere('email', 'like', "%{$term}%")
                    ->orWhere('subject', 'like', "%{$term}%");
            });
        });
    }

    /*
    |--------------------------------------------------------------------------
    | State Helpers
    |--------------------------------------------------------------------------
    */

    public function isUnread(): bool
    {
        return $this->status === self::STATUS_UNREAD;
    }

    public function isArchived(): bool
    {
        return $this->status === self::STATUS_ARCHIVED;
    }

    public function isSpam(): bool
    {
        return $this->status === self::STATUS_SPAM;
    }
}
