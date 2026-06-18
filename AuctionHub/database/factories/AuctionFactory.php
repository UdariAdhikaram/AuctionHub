<?php

namespace Database\Factories;

use App\Models\Auction;
use App\Models\Category;
use App\Models\Vendor;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Auction>
 */
class AuctionFactory extends Factory
{
    protected $model = Auction::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $currentPrice = fake()->randomFloat(2, 50, 5000);

        return [
            'vendor_id' => Vendor::factory(),
            'category_id' => Category::factory(),
            'title' => fake()->sentence(4),
            'description' => fake()->paragraph(),
            'starts_at' => now()->subHour(),
            'ends_at' => now()->addDay(),
            'reserve_price' => fake()->randomFloat(2, 50, 5000),
            'current_price' => $currentPrice,
            'bid_increment' => fake()->randomFloat(2, 1, 50),
            'status' => 'draft',
        ];
    }
}
