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
    Schema::create('section_user', function (Blueprint $t) {
        $t->id();
        $t->foreignId('section_id')->constrained()->cascadeOnDelete();
        $t->foreignId('user_id')->constrained()->cascadeOnDelete();
        $t->string('status')->default('pending'); // pending | approved | rejected
        $t->timestamp('joined_at')->nullable();
        $t->timestamps();
        $t->unique(['section_id', 'user_id']); // prevent duplicates
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('section_user');
    }
};
