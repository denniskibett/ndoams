<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('images', function (Blueprint $table) {
            $table->foreignId('updated_by')->nullable()->after('uploaded_by')->constrained('users');
            $table->string('certificate_serial')->nullable()->after('filename');
        });
    }

    public function down()
    {
        Schema::table('images', function (Blueprint $table) {
            $table->dropConstrainedForeignId('updated_by');
            $table->dropColumn('certificate_serial');
        });
    }
};
