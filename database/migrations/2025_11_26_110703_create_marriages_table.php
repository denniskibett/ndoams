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
        Schema::create('marriages', function (Blueprint $table) {
            $table->id();
            $table->string('certificate_serial')->nullable();
            $table->foreignId('marriage_type_id')->nullable()->constrained('categories');
            $table->foreignId('marriage_status_id')->nullable()->constrained('categories');
            $table->foreignId('verification_status_id')->nullable()->constrained('categories');
            $table->integer('year')->nullable();
            $table->integer('month')->nullable();
            $table->string('county')->nullable();
            $table->string('sub_county')->nullable();
            $table->string('constituency')->nullable();
            $table->date('reg_date')->nullable();
            $table->date('marriage_date')->nullable();
            $table->string('venue')->nullable();
            $table->string('image_main')->nullable();
            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->uuid('verified_by')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('marriages');
    }
};
