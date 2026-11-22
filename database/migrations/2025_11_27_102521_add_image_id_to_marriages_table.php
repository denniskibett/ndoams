<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('marriages', function (Blueprint $table) {
            $table->foreignId('image_id')->nullable()->after('id')->constrained('images');
        });
    }

    public function down()
    {
        Schema::table('marriages', function (Blueprint $table) {
            $table->dropConstrainedForeignId('image_id');
        });
    }
};
