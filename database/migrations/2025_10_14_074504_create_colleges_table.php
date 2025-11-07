<?php

// database/migrations/xxxx_xx_xx_xxxxxx_create_colleges_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('colleges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campus_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->timestamps();
            $table->unique(['campus_id','name']); // unique per campus
        });
    }
    public function down(): void { Schema::dropIfExists('colleges'); }
};

