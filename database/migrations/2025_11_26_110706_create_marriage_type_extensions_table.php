<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('marriage_type_extensions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('marriage_id')->constrained()->onDelete('cascade');
            $table->string('mahr_agreed')->nullable();
            $table->string('mahr_paid')->nullable();
            $table->string('mahr_deferred')->nullable();
            $table->string('gifts')->nullable();
            $table->string('muslim_officer')->nullable();
            $table->string('church_org')->nullable();
            $table->string('pastor_name')->nullable();
            $table->string('entry_no')->nullable();
            $table->string('temple')->nullable();
            $table->string('dowry')->nullable();
            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->uuid('verified_by')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('marriage_type_extensions');
    }
};