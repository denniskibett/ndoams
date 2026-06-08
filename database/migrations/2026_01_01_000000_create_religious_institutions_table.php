<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReligiousInstitutionsTable extends Migration
{
    public function up()
    {
        Schema::create('religious_institutions', function (Blueprint $table) {
            $table->id();
            $table->string('registered_name');
            $table->date('registration_date')->nullable();
            $table->string('registration_number')->nullable();
            $table->enum('registration_type', ['old', 'online'])->nullable();
            $table->string('religion')->nullable();
            $table->string('religion_type')->nullable();
            $table->unsignedBigInteger('ward_id')->nullable();
            $table->timestamps();
            
            $table->foreign('ward_id')->references('id')->on('counties')->nullOnDelete();
            $table->index('registered_name');
            $table->index('religion');
            $table->index('religion_type');
        });
    }

    public function down()
    {
        Schema::dropIfExists('religious_institutions');
    }
}