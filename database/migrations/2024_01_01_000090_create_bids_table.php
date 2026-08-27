<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bids', function (Blueprint $table) {
            $table->id();
            $table->foreignId('auction_round_id')->constrained('auction_rounds')->cascadeOnDelete();
            $table->foreignId('league_id')->constrained('leagues')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('pickable_type'); // driver|constructor
            $table->unsignedBigInteger('pickable_id');
            $table->unsignedInteger('amount');
            $table->timestamps();

            $table->unique(['auction_round_id', 'user_id', 'pickable_type', 'pickable_id'], 'bids_unique_bid');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bids');
    }
};
