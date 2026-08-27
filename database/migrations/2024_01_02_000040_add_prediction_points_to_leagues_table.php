<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leagues', function (Blueprint $table) {
            // Points per correct prediction, by type — e.g. {"pole": 5, "fastest_pit_stop": 5, "dnf": 5}.
            // Null/missing values fall back to config('fantasy.prediction_points') defaults.
            $table->json('prediction_points')->nullable()->after('budget');
        });
    }

    public function down(): void
    {
        Schema::table('leagues', function (Blueprint $table) {
            $table->dropColumn('prediction_points');
        });
    }
};
