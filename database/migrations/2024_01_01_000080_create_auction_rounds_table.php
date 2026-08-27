<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('auction_rounds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('league_id')->constrained('leagues')->cascadeOnDelete();
            $table->unsignedSmallInteger('round_number');
            $table->timestamp('opens_at');
            $table->timestamp('closes_at');
            $table->string('status')->default('open'); // open|resolved
            $table->timestamps();

            $table->unique(['league_id', 'round_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('auction_rounds');
    }
};
