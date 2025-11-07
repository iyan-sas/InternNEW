<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        if (!Schema::hasColumn('sections', 'school_year')) {
            Schema::table('sections', function (Blueprint $table) {
                $table->string('school_year', 9)->nullable()->after('section_name'); // "2024-2025"
                $table->index('school_year');
            });
        }
    }
    public function down(): void {
        if (Schema::hasColumn('sections', 'school_year')) {
            Schema::table('sections', function (Blueprint $table) {
                $table->dropIndex(['school_year']);
                $table->dropColumn('school_year');
            });
        }
    }
};
