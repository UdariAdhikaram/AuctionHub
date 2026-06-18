<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBidRequest;
use App\Models\Bid;
use App\Models\Auction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;

class BidController extends Controller
{
    public function store(StoreBidRequest $request)
    {
        // Rate limiting check
        $key = 'bids:' . $request->user()->id;

        if (RateLimiter::tooManyAttempts($key, 30)) {
            return response()->json([
                'message' => 'Too many bid attempts. Please wait.',
                'retry_after' => RateLimiter::availableIn($key)
            ], 429)->header('Retry-After', RateLimiter::availableIn($key));
        }

        RateLimiter::hit($key, 60);

        try {
            $bid = DB::transaction(function () use ($request) {
                $bid = new Bid([
                    'user_id' => $request->user()->id,
                    'auction_id' => $request->auction_id,
                    'amount' => $request->amount,
                ]);

                $bid->save();
                return $bid;
            });

            return response()->json([
                'message' => 'Bid placed successfully',
                'bid' => $bid,
                'new_current_price' => $request->getAuction()->fresh()->current_price,
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to place bid',
                'error' => $e->getMessage()
            ], 422);
        }
    }

    public function getWinningBids(Auction $auction)
    {
        $this->authorize('view', $auction);

        $winningBid = $auction->bids()
                              ->orderBy('amount', 'desc')
                              ->first();

        return response()->json([
            'auction_id' => $auction->id,
            'winning_bid' => $winningBid,
        ]);
    }
}
