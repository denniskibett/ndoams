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
        Schema::create('witnesses', function(Blueprint $table){
            $table->id();
            $table->foreignId('marriage_id')->constrained('marriages');
            $table->enum('spouse_side', ['husband', 'wife']);
            $table->string('name')->nullable();
            $table->foreignId('id_type_id')->nullable()->constrained('categories');
            $table->string('id_number')->nullable();
            $table->string('signature_image')->nullable();
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
        Schema::dropIfExists('witnesses');
    }
};
