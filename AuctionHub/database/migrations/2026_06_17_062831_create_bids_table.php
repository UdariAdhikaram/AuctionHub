<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('bids', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('auction_id')->constrained()->onDelete('cascade');
            $table->decimal('amount', 12, 2);
            $table->timestamp('placed_at')->useCurrent();
            $table->timestamps();

            // Unique constraint preventing identical (user, auction, amount) duplicates
            $table->unique(['user_id', 'auction_id', 'amount'], 'unique_user_auction_amount');
            $table->unique(['auction_id', 'amount'], 'unique_auction_amount');

            $table->index(['auction_id', 'amount']);
            $table->index(['user_id', 'placed_at']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('bids');
    }
};
