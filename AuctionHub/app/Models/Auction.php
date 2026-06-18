<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Query\Builder;

class Auction extends Model
{
    use HasFactory;

    protected $fillable = [
        'vendor_id', 'category_id', 'title', 'description',
        'starts_at', 'ends_at', 'reserve_price', 'current_price',
        'bid_increment', 'status'
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'reserve_price' => 'decimal:2',
            'current_price' => 'decimal:2',
            'bid_increment' => 'decimal:2',
            'is_live' => 'boolean',
        ];
    }

    // Relationships
    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function bids(): HasMany
    {
        return $this->hasMany(Bid::class);
    }

    public function attachments(): MorphMany
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }

    public function watchers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'watchlists')
                    ->withPivot('notify_at_close')
                    ->withTimestamps();
    }

    // Scopes
    public function scopeLive($query)
    {
        return $query->where('status', 'live')
                     ->where('starts_at', '<=', now())
                     ->where('ends_at', '>=', now());
    }

    public function scopeEndingSoon($query, int $minutes = 10)
    {
        return $query->where('status', 'live')
                     ->where('ends_at', '>=', now())
                     ->where('ends_at', '<=', now()->addMinutes($minutes));
    }

    // Accessor for minimum next bid
    public function getMinimumNextBidAttribute()
    {
        return $this->current_price + $this->bid_increment;
    }

    // Helper methods
    public function isLive(): bool
    {
        return $this->status === 'live' &&
               now()->between($this->starts_at, $this->ends_at);
    }

    public function isEnded(): bool
    {
        return $this->status === 'ended' || now()->gt($this->ends_at);
    }

    public function getCurrentHighestBid()
    {
        return $this->bids()->orderBy('amount', 'desc')->first();
    }
}
