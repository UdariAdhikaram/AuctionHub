<?php

namespace App\Http\Requests;

use App\Rules\VendorNotBlackout;
use Illuminate\Foundation\Http\FormRequest;

class StoreAuctionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'category_id' => ['required', 'exists:categories,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'starts_at' => ['required', 'date', new VendorNotBlackout($this->user())],
            'ends_at' => ['required', 'date', 'after:starts_at'],
            'reserve_price' => ['required', 'numeric'],
            'current_price' => ['required', 'numeric'],
            'bid_increment' => ['required', 'numeric'],
        ];
    }
}
