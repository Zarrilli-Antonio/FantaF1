<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('drivers', function (Blueprint $table) {
            $table->id();
            $table->string('api_ref')->unique();
            $table->string('first_name');
            $table->string('last_name');
            $table->unsignedTinyInteger('number')->nullable();
            $table->string('nationality')->nullable();
            $table->string('photo_url')->nullable();
            $table->foreignId('constructor_id')->nullable()->constrained('constructors')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('drivers');
    }
};
