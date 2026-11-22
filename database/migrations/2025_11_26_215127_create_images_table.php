<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Already exists on the live server (built from an SQL dump); skip there.
        if (Schema::hasTable('images')) {
            return;
        }

        Schema::create('images', function (Blueprint $table) {
            $table->id();
            $table->string('image_path');
            $table->string('name');
            $table->string('filename');
            $table->string('certificate_serial')->nullable();
            $table->integer('year');
            $table->integer('month');
            $table->foreignId('uploaded_by')->constrained('users')->cascadeOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->cascadeOnDelete();
            $table->integer('file_size')->nullable();
            $table->string('fit_type', 50)->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('images');
    }
};