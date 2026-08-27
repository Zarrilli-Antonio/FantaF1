<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('races', function (Blueprint $table) {
            $table->string('locality')->nullable()->after('country');
            $table->decimal('latitude', 9, 5)->nullable()->after('locality');
            $table->decimal('longitude', 9, 5)->nullable()->after('latitude');
            $table->string('wikipedia_url')->nullable()->after('longitude');
            $table->string('circuit_wikipedia_url')->nullable()->after('wikipedia_url');
        });
    }

    public function down(): void
    {
        Schema::table('races', function (Blueprint $table) {
            $table->dropColumn(['locality', 'latitude', 'longitude', 'wikipedia_url', 'circuit_wikipedia_url']);
        });
    }
};
