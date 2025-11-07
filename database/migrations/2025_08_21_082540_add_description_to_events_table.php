<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            // Add if missing
            if (!Schema::hasColumn('events', 'description')) {
                $table->text('description')->nullable()->after('title');
            }
            // OPTIONAL: only if table still doesn't have 'date'
            if (!Schema::hasColumn('events', 'date')) {
                $table->dateTime('date')->nullable()->after('description');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            if (Schema::hasColumn('events', 'date')) {
                $table->dropColumn('date');
            }
            if (Schema::hasColumn('events', 'description')) {
                $table->dropColumn('description');
            }
        });
    }
};