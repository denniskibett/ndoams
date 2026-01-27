<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pdf_pages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pdf_upload_id')->constrained('pdf_uploads')->onDelete('cascade');
            $table->integer('page_number');
            $table->enum('status', ['pending', 'assigned', 'in_progress', 'completed', 'review_needed', 'skipped'])->default('pending');
            $table->foreignId('assigned_to')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('completed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('assigned_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->integer('time_spent_seconds')->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->unique(['pdf_upload_id', 'page_number']);
            $table->index(['status', 'assigned_to']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pdf_pages');
    }
};