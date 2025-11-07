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
      Schema::table('streams', function (Blueprint $table) {
        $table->string('stream_token')->unique()->nullable()->after('invite_token');
       });
    }

    public function down()
   {
      Schema::table('streams', function (Blueprint $table) {
        $table->dropColumn('stream_token');
      });
    }

};
