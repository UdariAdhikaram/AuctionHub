<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Vendor;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Vendor>
 */
class VendorFactory extends Factory
{
    protected $model = Vendor::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'store_slug' => Str::slug(fake()->unique()->company() . '-' . fake()->unique()->numberBetween(1000, 9999)),
            'commission_rate' => 0.1000,
            'approved_at' => now(),
        ];
    }
}
