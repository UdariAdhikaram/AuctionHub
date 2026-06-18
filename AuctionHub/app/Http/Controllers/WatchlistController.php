<?php

namespace App\Http\Controllers;

use App\Models\Watchlist;
use App\Models\Auction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class WatchlistController extends Controller
{
    public function index(Request $request)
    {
        $watchlists = Watchlist::where('user_id', $request->user()->id)
                              ->with('auction')
                              ->get();

        return response()->json($watchlists);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'auction_id' => 'required|exists:auctions,id',
            'notify_at_close' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Check if already in watchlist
        $existing = Watchlist::where('user_id', $request->user()->id)
                            ->where('auction_id', $request->auction_id)
                            ->first();

        if ($existing) {
            return response()->json([
                'message' => 'Already in watchlist',
                'watchlist' => $existing
            ], 409);
        }

        $watchlist = Watchlist::create([
            'user_id' => $request->user()->id,
            'auction_id' => $request->auction_id,
            'notify_at_close' => $request->notify_at_close ?? false,
        ]);

        return response()->json([
            'message' => 'Added to watchlist',
            'watchlist' => $watchlist
        ], 201);
    }

    public function destroy(Request $request, Watchlist $watchlist)
    {
        if ($watchlist->user_id !== $request->user()->id) {
            return response()->json([
                'message' => 'Unauthorized'
            ], 403);
        }

        $watchlist->delete();

        return response()->json([
            'message' => 'Removed from watchlist'
        ]);
    }
}
