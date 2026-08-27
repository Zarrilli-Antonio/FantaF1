<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leagues', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('owner_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('season_id')->constrained('seasons')->cascadeOnDelete();
            $table->string('invite_code', 12)->unique();
            $table->unsignedTinyInteger('driver_slots')->default(5);
            $table->unsignedTinyInteger('constructor_slots')->default(1);
            $table->unsignedTinyInteger('starters_count')->default(3);
            $table->unsignedInteger('budget')->default(1000);
            $table->string('auction_status')->default('pending'); // pending|round_open|completed
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leagues');
    }
};
