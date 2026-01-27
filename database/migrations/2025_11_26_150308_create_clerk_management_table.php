<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clerk_management', function (Blueprint $table) {
            $table->id();
            $table->foreignId('data_clerk_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('marriage_teller_id')->constrained('users')->onDelete('cascade');
            $table->integer('year');
            $table->integer('month');
            $table->integer('filled_count')->default(0);
            $table->integer('target_count')->default(0);
            $table->enum('status', ['active', 'completed', 'locked'])->default('active');
            $table->text('notes')->nullable();
            $table->foreignId('pdf_upload_id')->nullable()->constrained('pdf_uploads');
            $table->enum('assignment_type', ['manual', 'pdf_bulk'])->default('manual');
            $table->json('assigned_pages')->nullable(); // [1, 5, 10, 15] or range "1
            $table->timestamps();

            $table->unique(['data_clerk_id', 'year', 'month']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clerk_management');
    }
};