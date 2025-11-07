<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('student_verifications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('stream_id');          // class/stream
            $table->unsignedBigInteger('section_id')->nullable();
            $table->unsignedBigInteger('user_id');            // the student user
            $table->string('cor_file');                       // path in storage
            $table->string('id_file');                        // path in storage
            $table->enum('status', ['pending','approved','rejected'])->default('pending');
            $table->unsignedBigInteger('reviewer_id')->nullable();  // coordinator who reviewed
            $table->timestamp('reviewed_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['stream_id','user_id']); // one active submission per class
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_verifications');
    }
};

