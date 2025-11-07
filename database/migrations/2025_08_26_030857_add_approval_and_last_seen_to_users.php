<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('users', function (Blueprint $t) {
            if (!Schema::hasColumn('users','status')) {
                $t->enum('status',['pending','approved','rejected'])
                  ->default('pending')->after('role');
            }
            if (!Schema::hasColumn('users','last_seen_at')) {
                $t->timestamp('last_seen_at')->nullable()->after('status');
            }
        });
    }
    public function down(): void {
        Schema::table('users', function (Blueprint $t) {
            if (Schema::hasColumn('users','last_seen_at')) $t->dropColumn('last_seen_at');
            if (Schema::hasColumn('users','status')) $t->dropColumn('status');
        });
    }
};

