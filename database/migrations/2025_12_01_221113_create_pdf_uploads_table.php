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
        if (Schema::hasTable('pdf_uploads')) {
            return;
        }

        Schema::create('pdf_uploads', function (Blueprint $table) {
            $table->id(); // Auto-increment primary key
            $table->uuid('uuid')->unique();
            $table->foreignId('marriage_type_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->integer('year');
            $table->unsignedTinyInteger('month'); // 1-12
            $table->string('county_code', 20);
            $table->string('filename');      // internal filename
            $table->integer('file_size');    // in bytes
            $table->string('name');          // original name
            $table->string('storage_path', 500)->nullable();  // e.g., pdf-uploads/2025/12/filename.pdf
            $table->integer('total_pages')->default(1);
            $table->string('status', 20)->nullable()->default('pending');
            $table->foreignId('uploaded_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
            $table->softDeletes();
            $table->string('file_hash', 64)->index();
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
