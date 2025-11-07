<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('section_rosters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('section_id')->constrained()->cascadeOnDelete();
            $table->string('student_no')->index();
            $table->string('full_name');
            $table->string('course')->nullable();
            $table->string('midterm')->nullable();
            $table->string('final')->nullable();
            $table->string('re_exam')->nullable();
            $table->string('remarks')->nullable();
            $table->enum('status', ['listed','joined','approved'])->default('listed')->index();
            $table->timestamps();

            $table->unique(['section_id','student_no']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('section_rosters');
    }
};
