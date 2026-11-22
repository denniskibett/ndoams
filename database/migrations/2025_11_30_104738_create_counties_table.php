<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Already exists on the live server (built from an SQL dump); skip there.
        if (Schema::hasTable('counties')) {
            return;
        }

        Schema::create('counties', function (Blueprint $table) {
        $table->id(); // auto increments
        $table->string('county_code', 10);
        $table->string('name');
        $table->string('constituency', 512)->nullable();
        $table->string('wards', 512)->nullable();
        $table->timestamps();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('counties');
    }
};
