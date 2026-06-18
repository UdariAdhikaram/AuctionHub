<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name', 'email', 'password', 'role', 'kyc_verified_at', 'deposit_balance'
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'kyc_verified_at' => 'datetime',
            'deposit_balance' => 'decimal:2',
            'password' => 'hashed',
        ];
    }

    // Relationships
    public function vendor(): HasOne
    {
        return $this->hasOne(Vendor::class);
    }

    public function bids(): HasMany
    {
        return $this->hasMany(Bid::class);
    }

    public function watchlists(): BelongsToMany
    {
        return $this->belongsToMany(Auction::class, 'watchlists')
                    ->withPivot('notify_at_close')
                    ->withTimestamps();
    }

    // Scope with active bid count (prevents N+1)
    public function scopeWithActiveBidCount($query)
    {
        return $query->withCount([
            'bids as active_bid_count' => function ($query) {
                $query->whereHas('auction', function ($q) {
                    $q->where('status', 'live')
                      ->where('starts_at', '<=', now())
                      ->where('ends_at', '>=', now());
                });
            }
        ]);
    }

    // Helper methods
    public function isVendor(): bool
    {
        return $this->role === 'vendor';
    }

    public function isBidder(): bool
    {
        return $this->role === 'bidder';
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isKycVerified(): bool
    {
        return !is_null($this->kyc_verified_at);
    }
}
