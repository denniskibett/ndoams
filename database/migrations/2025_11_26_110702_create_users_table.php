<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id(); // BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->foreignId('role_id')
                ->constrained('roles')
                ->default(5); // default role = "user"
            $table->string('passport_image')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
