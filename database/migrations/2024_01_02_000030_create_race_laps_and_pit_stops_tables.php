<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('race_laps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('race_id')->constrained('races')->cascadeOnDelete();
            $table->foreignId('driver_id')->constrained('drivers')->cascadeOnDelete();
            $table->unsignedSmallInteger('lap');
            $table->unsignedTinyInteger('position')->nullable();
            $table->string('time')->nullable();
            $table->timestamps();

            $table->unique(['race_id', 'driver_id', 'lap']);
        });

        Schema::create('race_pit_stops', function (Blueprint $table) {
            $table->id();
            $table->foreignId('race_id')->constrained('races')->cascadeOnDelete();
            $table->foreignId('driver_id')->constrained('drivers')->cascadeOnDelete();
            $table->unsignedTinyInteger('stop');
            $table->unsignedSmallInteger('lap');
            $table->string('time_of_day')->nullable();
            $table->string('duration')->nullable();
            $table->timestamps();

            $table->unique(['race_id', 'driver_id', 'stop']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('race_pit_stops');
        Schema::dropIfExists('race_laps');
    }
};
