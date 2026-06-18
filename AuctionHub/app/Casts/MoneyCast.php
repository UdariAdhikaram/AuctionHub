<?php

namespace App\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use App\ValueObjects\Money;

class MoneyCast implements CastsAttributes
{
    /**
     * Cast the given value to a Money object.
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        return new Money($value, 'USD');
    }

    /**
     * Prepare the given value for storage.
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        if ($value instanceof Money) {
            return $value->getAmount();
        }
        
        return $value;
    }
}