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
        Schema::create('student_invites', function (Blueprint $table) {
       $table->id();
       $table->foreignId('stream_id')->constrained()->onDelete('cascade');
       $table->string('student_name');
       $table->uuid('token')->unique(); // this is the unique access link
       $table->timestamps();
      });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_invites');
    }
};
