<?php

// database/migrations/xxxx_xx_xx_xxxxxx_create_departments_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('departments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('college_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->timestamps();
            $table->unique(['college_id','name']); // unique per college
        });
    }
    public function down(): void { Schema::dropIfExists('departments'); }
};

