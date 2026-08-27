<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('races', function (Blueprint $table) {
            $table->id();
            $table->foreignId('season_id')->constrained('seasons')->cascadeOnDelete();
            $table->string('api_ref')->unique();
            $table->unsignedTinyInteger('round');
            $table->string('name');
            $table->string('circuit');
            $table->string('country')->nullable();
            $table->dateTime('starts_at');
            $table->string('status')->default('scheduled'); // scheduled|completed
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('races');
    }
};
