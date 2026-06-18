<?php

namespace App\Models;

use App\Observers\BidObserver;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Bid extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'auction_id', 'amount', 'placed_at'];

    protected static function booted(): void
    {
        static::observe(BidObserver::class);
    }

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'placed_at' => 'datetime',
        ];
    }

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function auction(): BelongsTo
    {
        return $this->belongsTo(Auction::class);
    }

    // Scope for winning bids (current highest per auction)
    public function scopeWinning($query)
    {
        return $query->whereIn('id', function ($subQuery) {
            $subQuery->selectRaw('MAX(id)')
                     ->from('bids as b')
                     ->whereColumn('b.auction_id', 'bids.auction_id')
                     ->groupBy('b.auction_id')
                     ->havingRaw('b.amount = MAX(bids.amount)');
        });
    }

    // Alternative winning scope using window function
    public function scopeWinningWithWindow($query)
    {
        return $query->whereIn('id', function ($subQuery) {
            $subQuery->select('id')
                     ->from('bids')
                     ->whereIn('auction_id', function ($auctionQuery) {
                         $auctionQuery->select('id')->from('auctions')->where('status', 'live');
                     })
                     ->whereRaw('amount = (SELECT MAX(amount) FROM bids b2 WHERE b2.auction_id = bids.auction_id)');
        });
    }
}
