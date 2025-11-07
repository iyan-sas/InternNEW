<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('streams', function (Blueprint $table) {
            if (! Schema::hasColumn('streams', 'section_id')) {
                $table->foreignId('section_id')->nullable()->constrained('sections')->nullOnDelete();
                $table->index('section_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('streams', function (Blueprint $table) {
            if (Schema::hasColumn('streams', 'section_id')) {
                $table->dropConstrainedForeignId('section_id');
            }
        });
    }
};
