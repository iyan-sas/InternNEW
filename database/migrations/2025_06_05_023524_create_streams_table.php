<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStreamsTable extends Migration
{
    public function up()
    {
        Schema::create('streams', function (Blueprint $table) {
     $table->id();
     $table->string('class_name');
     $table->string('section')->nullable();
     $table->string('subject')->nullable();
     $table->string('room')->nullable();
     $table->string('invite_token')->unique();
     $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
     $table->foreignId('coordinator_id')->nullable()->constrained('users')->onDelete('set null');
     $table->string('coordinator_token')->unique()->nullable();
     $table->string('student_token')->unique()->nullable();
     $table->timestamps();

     });

    }

    public function down()
    {
        Schema::dropIfExists('streams');
    }
}


