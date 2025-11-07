<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        // No-op: the composite unique already exists (sections_stream_sy_name_unique)
        // and there's no old global unique to drop on this database.
    }

    public function down(): void
    {
        // No-op
    }
};
