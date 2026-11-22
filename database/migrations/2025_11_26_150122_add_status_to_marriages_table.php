<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('marriages', function (Blueprint $table) {
            // Add new columns for workflow management
            $table->enum('system_status', ['Pending', 'Completed', 'Cancelled', 'Approved'])->default('Pending');
            $table->enum('verification_status', ['Unverified', 'Verified', 'Rejected'])->default('Unverified');
            $table->string('original_filename')->nullable();
            $table->integer('page_number')->nullable();
            
            // Change UUID columns to foreign keys if they reference users table
            $table->foreignId('created_by')->nullable()->change();
            $table->foreignId('updated_by')->nullable()->change();
            $table->foreignId('verified_by')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('marriages', function (Blueprint $table) {
            $table->dropColumn([
                'system_status',
                'verification_status', 
                'original_filename',
                'page_number',
                'husband_name'
            ]);
        });
    }
};