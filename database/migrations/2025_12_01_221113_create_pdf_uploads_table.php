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
        Schema::create('pdf_uploads', function (Blueprint $table) {
            $table->id(); // Auto-increment primary key
            $table->uuid('uuid')->unique();
            $table->integer('year');
            $table->tinyInteger('month'); // 1-12
            $table->string('county_code', 20);
            $table->string('filename');      // internal filename
            $table->integer('file_size');    // in bytes
            $table->string('name');          // original name
            $table->string('storage_path');  // e.g., pdf-uploads/2025/12/filename.pdf
            $table->integer('total_pages')->default(1);
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->foreignId('uploaded_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps(); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pdf_uploads');
    }
};
