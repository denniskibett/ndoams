<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('images', function (Blueprint $table) {
            $table->id();
            $table->string('image_path');
            $table->string('name');
            $table->string('filename');
            $table->string('certificate_serial')->nullable();
            $table->integer('year');
            $table->integer('month');
            $table->foreignId('uploaded_by')->constrained('users');
             $table->foreignId('updated_by')->nullable()->after('uploaded_by')->constrained('users');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('images');
    }
};