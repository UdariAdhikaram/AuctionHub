<?php

namespace App\Http\Filters;

use App\Models\Auction;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;

class AuctionFilter
{
    public function apply(Builder $query, Request $request): Builder
    {
        if ($v = $request->query('category')) {
            $query->where('category_id', $v);
        }

        if ($v = $request->query('status')) {
            $query->where('status', $v);
        }

        if ($v = $request->query('vendor_slug')) {
            $query->whereHas('vendor', fn($q) => $q->where('store_slug', $v));
        }

        if ($min = $request->query('price_min')) {
            $query->where('current_price', '>=', $min);
        }

        if ($max = $request->query('price_max')) {
            $query->where('current_price', '<=', $max);
        }

        return $query;
    }
}
