<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('drivers', function (Blueprint $table) {
            $table->string('code', 3)->nullable()->after('last_name');
            $table->date('date_of_birth')->nullable()->after('nationality');
            $table->string('wikipedia_url')->nullable()->after('photo_url');
        });
    }

    public function down(): void
    {
        Schema::table('drivers', function (Blueprint $table) {
            $table->dropColumn(['code', 'date_of_birth', 'wikipedia_url']);
        });
    }
};
