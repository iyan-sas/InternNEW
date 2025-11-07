<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('admin_documents', function (Blueprint $table) {
            $table->id();
            $table->string('stream_id');
            $table->string('type');
            $table->string('title');
            $table->string('filename'); // stores the file path
            $table->timestamps();
            
            $table->index(['stream_id', 'type']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('admin_documents');
    }
};