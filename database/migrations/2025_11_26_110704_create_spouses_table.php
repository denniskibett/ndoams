<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('spouses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('marriage_id')->constrained()->onDelete('cascade');
            $table->enum('spouse_type', ['husband', 'wife']);
            $table->string('name')->nullable();
            $table->foreignId('id_type_id')->nullable()->constrained('categories')->onDelete('set null');
            $table->string('id_number')->nullable();
            $table->string('signature_image')->nullable();
            $table->string('residence')->nullable();
            $table->string('county')->nullable();
            $table->string('occupation')->nullable();
            $table->string('father_name')->nullable();
            $table->string('father_occupation')->nullable();
            $table->string('father_residence')->nullable();
            $table->foreignId('father_id_type_id')->nullable()->constrained('categories')->onDelete('set null');
            $table->string('father_id_number')->nullable();
            $table->string('mother_name')->nullable();
            $table->string('mother_occupation')->nullable();
            $table->string('mother_residence')->nullable();
            $table->foreignId('mother_id_type_id')->nullable()->constrained('categories')->onDelete('set null');
            $table->string('mother_id_number')->nullable();
            $table->integer('age')->nullable();
            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->uuid('verified_by')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('spouses');
    }
};