<?php

namespace App\Http\Controllers;

use App\Models\Auction;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AuctionController extends Controller
{
    public function index()
    {
        $auctions = Auction::with(['vendor.user', 'category'])
                          ->orderBy('created_at', 'desc')
                          ->paginate(20);

        return response()->json($auctions);
    }

    public function live()
    {
        $auctions = Auction::live()
                          ->with(['vendor.user', 'category'])
                          ->orderBy('ends_at', 'asc')
                          ->get();

        return response()->json($auctions);
    }

    public function endingSoon(Request $request)
    {
        $minutes = $request->get('minutes', 10);
        $auctions = Auction::endingSoon($minutes)
                          ->with(['vendor.user', 'category'])
                          ->orderBy('ends_at', 'asc')
                          ->get();

        return response()->json($auctions);
    }

    public function show(Auction $auction)
    {
        $auction->load(['vendor.user', 'category', 'bids.user']);

        return response()->json($auction);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'category_id' => 'required|exists:categories,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'starts_at' => 'required|date|after:now',
            'ends_at' => 'required|date|after:starts_at',
            'reserve_price' => 'required|numeric|min:0.01',
            'current_price' => 'required|numeric|min:0.01',
            'bid_increment' => 'required|numeric|min:0.01',
            'status' => 'required|in:draft,scheduled,live,ended,cancelled',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $auction = Auction::create([
            'vendor_id' => $request->user()->vendor->id,
            'category_id' => $request->category_id,
            'title' => $request->title,
            'description' => $request->description,
            'starts_at' => $request->starts_at,
            'ends_at' => $request->ends_at,
            'reserve_price' => $request->reserve_price,
            'current_price' => $request->current_price,
            'bid_increment' => $request->bid_increment,
            'status' => $request->status,
        ]);

        return response()->json([
            'message' => 'Auction created successfully',
            'auction' => $auction
        ], 201);
    }

    public function update(Request $request, Auction $auction)
    {
        // Check if user owns this auction
        if ($auction->vendor_id !== $request->user()->vendor->id) {
            return response()->json([
                'message' => 'You do not own this auction'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'category_id' => 'sometimes|exists:categories,id',
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'starts_at' => 'sometimes|date|after:now',
            'ends_at' => 'sometimes|date|after:starts_at',
            'reserve_price' => 'sometimes|numeric|min:0.01',
            'bid_increment' => 'sometimes|numeric|min:0.01',
            'status' => 'sometimes|in:draft,scheduled,live,ended,cancelled',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $auction->update($request->all());

        return response()->json([
            'message' => 'Auction updated successfully',
            'auction' => $auction
        ]);
    }

    public function destroy(Request $request, Auction $auction)
    {
        if ($auction->vendor_id !== $request->user()->vendor->id) {
            return response()->json([
                'message' => 'You do not own this auction'
            ], 403);
        }

        $auction->delete();

        return response()->json([
            'message' => 'Auction deleted successfully'
        ]);
    }
}
