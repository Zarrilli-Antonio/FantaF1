<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('race_predictions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('league_id')->constrained('leagues')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('race_id')->constrained('races')->cascadeOnDelete();
            $table->string('type'); // pole|fastest_pit_stop|dnf|... (extensible, not a DB enum)
            $table->string('pickable_type'); // driver|constructor
            $table->unsignedBigInteger('pickable_id');
            $table->timestamps();

            $table->unique(['league_id', 'user_id', 'race_id', 'type'], 'race_predictions_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('race_predictions');
    }
};
