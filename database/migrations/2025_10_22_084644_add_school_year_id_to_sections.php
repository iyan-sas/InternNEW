<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /** Check if a specific index exists on a table */
    private function indexExists(string $table, string $indexName): bool
    {
        $rows = DB::select('SHOW INDEX FROM `'.$table.'` WHERE Key_name = ?', [$indexName]);
        return count($rows) > 0;
    }

    /** Convenience: drop an index only if it exists */
    private function safeDropUnique(string $table, string $indexName): void
    {
        if ($this->indexExists($table, $indexName)) {
            Schema::table($table, function (Blueprint $table) use ($indexName) {
                $table->dropUnique($indexName);
            });
        }
    }

    public function up(): void
    {
        // 1) Some databases never had a global unique on section_name.
        //    Drop ONLY if present (name may vary per DB install; we check both common names).
        $this->safeDropUnique('sections', 'sections_section_name_unique'); // typical Laravel name
        $this->safeDropUnique('sections', 'section_name_unique');          // fallback just in case

        // 2) Add our composite unique:
        if (Schema::hasColumn('sections', 'school_year_id')) {
            // Unique within (stream_id, school_year_id, section_name)
            if (! $this->indexExists('sections', 'sections_stream_syid_name_unique')) {
                Schema::table('sections', function (Blueprint $table) {
                    $table->unique(
                        ['stream_id', 'school_year_id', 'section_name'],
                        'sections_stream_syid_name_unique'
                    );
                });
            }
        } else {
            // If you used a string column instead of *_id
            if (! $this->indexExists('sections', 'sections_stream_sy_name_unique')) {
                Schema::table('sections', function (Blueprint $table) {
                    $table->unique(
                        ['stream_id', 'school_year', 'section_name'],
                        'sections_stream_sy_name_unique'
                    );
                });
            }
        }
    }

    public function down(): void
    {
        // Drop the composite unique(s) if present
        if ($this->indexExists('sections', 'sections_stream_syid_name_unique')) {
            Schema::table('sections', function (Blueprint $table) {
                $table->dropUnique('sections_stream_syid_name_unique');
            });
        }

        if ($this->indexExists('sections', 'sections_stream_sy_name_unique')) {
            Schema::table('sections', function (Blueprint $table) {
                $table->dropUnique('sections_stream_sy_name_unique');
            });
        }

        // (Optional) restore the old global unique — usually NOT wanted anymore:
        // if (! $this->indexExists('sections', 'sections_section_name_unique')) {
        //     Schema::table('sections', function (Blueprint $table) {
        //         $table->unique('section_name', 'sections_section_name_unique');
        //     });
        // }
    }
};
