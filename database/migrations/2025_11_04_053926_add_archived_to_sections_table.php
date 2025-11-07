<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('sections', function (Blueprint $table) {
            // Add the column without positioning so it works regardless of existing columns
            if (!Schema::hasColumn('sections', 'archived')) {
                $table->boolean('archived')->default(false);
            }
        });
    }

    public function down(): void
    {
        Schema::table('sections', function (Blueprint $table) {
            if (Schema::hasColumn('sections', 'archived')) {
                $table->dropColumn('archived');
            }
        });
    }
};
