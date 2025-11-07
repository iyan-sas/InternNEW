<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('ojt_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();     // the student
            $table->foreignId('stream_id')->constrained()->cascadeOnDelete();   // the class/section
            $table->string('name');
            $table->string('address');
            $table->string('contact', 32);
            $table->string('company_name');
            $table->string('company_address');
            $table->timestamps();

            $table->unique(['user_id', 'stream_id']); // one form per student per class
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ojt_profiles');
    }
};
