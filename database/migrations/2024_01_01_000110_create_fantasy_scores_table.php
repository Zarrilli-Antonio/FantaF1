<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fantasy_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('league_id')->constrained('leagues')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('race_id')->constrained('races')->cascadeOnDelete();
            $table->decimal('points', 6, 1)->default(0);
            $table->json('breakdown')->nullable();
            $table->timestamps();

            $table->unique(['league_id', 'user_id', 'race_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fantasy_scores');
    }
};
