<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('streams', function (Blueprint $table) {
            // Only add if the column doesn't exist yet
            if (! Schema::hasColumn('streams', 'coordinator_invite_token')) {
                // Place it where it makes sense in your table; adjust `after()` if needed
                $table->string('coordinator_invite_token', 64)
                    ->unique()
                    ->nullable()
                    ->after('invite_token'); // or ->after('id') if you prefer
            }
        });
    }

    public function down(): void
    {
        Schema::table('streams', function (Blueprint $table) {
            if (Schema::hasColumn('streams', 'coordinator_invite_token')) {
                // Drop the unique index first (default auto-generated name below)
                try {
                    $table->dropUnique('streams_coordinator_invite_token_unique');
                } catch (\Throwable $e) {
                    // If index name differs or doesn't exist, ignore so rollback still proceeds
                }

                // Then drop the column
                $table->dropColumn('coordinator_invite_token');
            }
        });
    }
};
