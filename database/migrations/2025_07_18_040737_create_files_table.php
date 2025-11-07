<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
     /**
     * Run the migrations.
     */
    public function up(): void
    {
    Schema::create('files', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('stream_id'); // ✅ for class
        $table->string('filename');
        $table->timestamps();

        // Optional: add foreign key constraint
        $table->foreign('stream_id')->references('id')->on('streams')->onDelete('cascade');
    });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('files');
    }
};
