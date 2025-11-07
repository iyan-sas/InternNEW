<?php
// database/migrations/2025_10_06_000001_add_section_id_to_tasks_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            // gawin nating required (per-section). Kung may luma kang data, temporary nullable muna
            $table->unsignedBigInteger('section_id')->nullable()->after('stream_id');

            $table->index(['stream_id', 'section_id']);
            // optional: i-avoid ang duplicate titles sa loob ng iisang section
            // $table->unique(['section_id', 'title']);
        });
    }

    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            // drop indexes kung nag-add ka ng unique/index sa itaas
            // $table->dropUnique(['section_id', 'title']);
            $table->dropIndex(['stream_id', 'section_id']);
            $table->dropColumn('section_id');
        });
    }
};
