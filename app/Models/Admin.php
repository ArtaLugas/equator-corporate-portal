<?php

namespace App\Models;

use App\Jobs\SendPasswordResetLink;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Admin extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'avatar',
        'role',
        'status',
        'last_login_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
    ];

    protected $casts = [
        'last_login_at' => 'datetime',
        'password' => 'hashed',
        'two_factor_secret' => 'encrypted',
        'two_factor_recovery_codes' => 'encrypted:array',
        'two_factor_confirmed_at' => 'datetime',
    ];

    /*
    |-----------------------------------------------------------
    |   Relationship
    |-----------------------------------------------------------
    */

    public function activityLogs(): HasMany
    {
        return $this->hasMany(ActivityLog::class);
    }

    public function messageReplies(): HasMany
    {
        return $this->hasMany(MessageReply::class);
    }

    /*
    |-----------------------------------------------------------
    |   Role Helpers
    |-----------------------------------------------------------
    */

    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin';
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * True once the admin has verified a TOTP code during enrollment. A secret
     * that exists but is unconfirmed does NOT gate login.
     */
    public function hasTwoFactorEnabled(): bool
    {
        return ! is_null($this->two_factor_confirmed_at) && ! is_null($this->two_factor_secret);
    }

    /*
    |-----------------------------------------------------------
    |   Password Reset
    |-----------------------------------------------------------
    */

    /**
     * Send the reset link through the CMS Brevo mailer (queued) instead of the
     * default notification mail channel, so it uses the same transport as every
     * other transactional email. The URL is built here, in the request context.
     */
    public function sendPasswordResetNotification($token): void
    {
        $broker = config('auth.defaults.passwords');
        $expires = (int) config("auth.passwords.{$broker}.expire", 60);

        $url = route('admin.password.reset', [
            'token' => $token,
            'email' => $this->getEmailForPasswordReset(),
        ]);

        SendPasswordResetLink::dispatch($this, $url, $expires);
    }
}
