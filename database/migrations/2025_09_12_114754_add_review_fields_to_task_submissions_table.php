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
    Schema::table('task_submissions', function (Blueprint $table) {
        $table->string('review_status')->nullable()->index();      // 'approved' | 'needs_revision' | null
        $table->text('review_remark')->nullable();
        $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
        $table->timestamp('reviewed_at')->nullable();
    });
}

public function down(): void
{
    Schema::table('task_submissions', function (Blueprint $table) {
        $table->dropColumn(['review_status', 'review_remark', 'reviewed_by', 'reviewed_at']);
    });
}

};
