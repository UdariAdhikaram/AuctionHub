<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Auction;
use App\Rules\MinimumNextBid;
use Illuminate\Validation\Rule;

class StoreBidRequest extends FormRequest
{
    private ?Auction $auction = null;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $auctionId = $this->input('auction_id');
        $this->auction = Auction::find($auctionId);

        if (!$this->auction) {
            return [
                'auction_id' => ['required', 'exists:auctions,id'],
            ];
        }

        return [
            'auction_id' => [
                'required',
                'exists:auctions,id',
                Rule::exists('auctions', 'id')->where(function ($query) {
                    return $query->where('status', 'live')
                                 ->where('starts_at', '<=', now())
                                 ->where('ends_at', '>=', now());
                }),
                function ($attribute, $value, $fail) {
                    if ($this->auction->vendor->user_id === $this->user()->id) {
                        $fail('You cannot bid on your own auction.');
                    }
                }
            ],
            'amount' => [
                'required',
                'numeric',
                'min:0.01',
                'decimal:0,2',
                new MinimumNextBid($this->auction),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'auction_id.exists' => 'The selected auction is not available for bidding.',
            'amount.decimal' => 'The amount must have exactly 2 decimal places.',
        ];
    }

    public function getAuction(): Auction
    {
        return $this->auction;
    }
}
