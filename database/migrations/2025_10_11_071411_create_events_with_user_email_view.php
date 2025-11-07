<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // siguraduhing existing na ang tables bago gumawa ng VIEW
        DB::statement('DROP VIEW IF EXISTS `events_with_user_email`');

        DB::statement("
            CREATE VIEW `events_with_user_email` AS
            SELECT
              e.id,
              e.title,
              e.description,
              e.user_id,
              u.email AS user_email,
              e.`date`,
              e.created_at,
              e.updated_at
            FROM `events` e
            LEFT JOIN `users` u ON u.id = e.user_id
        ");
    }

    public function down(): void
    {
        DB::statement('DROP VIEW IF EXISTS `events_with_user_email`');
    }
};
