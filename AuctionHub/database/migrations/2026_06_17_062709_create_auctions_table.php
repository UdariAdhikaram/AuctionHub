<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('auctions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_id')->constrained('vendors')->onDelete('cascade');
            $table->foreignId('category_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->text('description')->nullable();
            $table->dateTime('starts_at');
            $table->dateTime('ends_at');
            $table->decimal('reserve_price', 12, 2);
            $table->decimal('current_price', 12, 2);
            $table->decimal('bid_increment', 12, 2)->default(10.00);
            $table->enum('status', ['draft', 'scheduled', 'live', 'ended', 'cancelled'])->default('draft');
            $table->timestamps();

            $table->index(['status', 'starts_at', 'ends_at']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('auctions');
    }
};
