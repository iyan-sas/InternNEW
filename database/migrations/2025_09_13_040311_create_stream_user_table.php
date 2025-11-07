<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('stream_user')) {
            Schema::create('stream_user', function (Blueprint $table) {
                $table->id();

                // who and what class
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->foreignId('stream_id')->constrained('streams')->cascadeOnDelete();

                // which section that class belongs to (for Admin view)
                $table->foreignId('section_id')->nullable()->constrained('sections')->cascadeOnDelete();

                $table->timestamps();

                // avoid duplicate enrollments per class
                $table->unique(['user_id', 'stream_id']);

                // helpful indexes
                $table->index(['section_id', 'stream_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('stream_user');
    }
};
