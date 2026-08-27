<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('race_results', function (Blueprint $table) {
            $table->unsignedSmallInteger('laps')->nullable()->after('grid');
            $table->string('race_time')->nullable()->after('laps');
            $table->string('fastest_lap_time')->nullable()->after('fastest_lap');
            $table->unsignedSmallInteger('fastest_lap_number')->nullable()->after('fastest_lap_time');
        });
    }

    public function down(): void
    {
        Schema::table('race_results', function (Blueprint $table) {
            $table->dropColumn(['laps', 'race_time', 'fastest_lap_time', 'fastest_lap_number']);
        });
    }
};
