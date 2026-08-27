<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('race_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('race_id')->constrained('races')->cascadeOnDelete();
            $table->foreignId('driver_id')->constrained('drivers')->cascadeOnDelete();
            $table->foreignId('constructor_id')->constrained('constructors')->cascadeOnDelete();
            $table->unsignedTinyInteger('grid')->nullable();
            $table->unsignedTinyInteger('position')->nullable();
            $table->decimal('real_points', 5, 1)->default(0);
            $table->boolean('fastest_lap')->default(false);
            $table->string('status')->default('Finished'); // Finished|DNF|DSQ|...
            $table->timestamps();

            $table->unique(['race_id', 'driver_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('race_results');
    }
};
