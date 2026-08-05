<?php

namespace App\Models;

use App\Jobs\SendPasswordResetLink;
use App\Support\Rbac;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class Admin extends Authenticatable
{
    use HasFactory, HasRoles, Notifiable;

    /**
     * Pin spatie to the `admin` guard. Both `web` and `admin` guards share the
     * `admins` provider and `web` is declared first, so without this spatie would
     * derive the `web` guard for this model and reject `admin`-guard permissions
     * with GuardDoesNotMatch. See App\Support\Rbac for the full rationale.
     */
    protected string $guard_name = Rbac::GUARD;

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
