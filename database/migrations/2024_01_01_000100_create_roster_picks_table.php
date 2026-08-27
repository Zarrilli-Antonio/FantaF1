<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('roster_picks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('league_id')->constrained('leagues')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('auction_round_id')->constrained('auction_rounds')->cascadeOnDelete();
            $table->string('pickable_type'); // driver|constructor
            $table->unsignedBigInteger('pickable_id');
            $table->unsignedInteger('price_paid');
            $table->timestamps();

            $table->unique(['league_id', 'pickable_type', 'pickable_id'], 'roster_picks_unique_item_per_league');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('roster_picks');
    }
};
