<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::table('sections', function (Blueprint $table) {
        $table->string('student_upload_token')->unique()->nullable();
    });
}

public function down()
{
    Schema::table('sections', function (Blueprint $table) {
        $table->dropColumn('student_upload_token');
    });
}

};
