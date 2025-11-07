<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('student_verifications', function (Blueprint $table) {
            if (! Schema::hasColumn('student_verifications', 'company_name')) {
                $table->string('company_name', 255)->nullable();
                $table->index('company_name');
            }

            if (! Schema::hasColumn('student_verifications', 'city')) {
                $table->string('city', 100)->nullable();
                $table->index('city');
            }
        });
    }

    public function down(): void
    {
        Schema::table('student_verifications', function (Blueprint $table) {
            if (Schema::hasColumn('student_verifications', 'city')) {
                $table->dropIndex(['city']);
                $table->dropColumn('city');
            }
            if (Schema::hasColumn('student_verifications', 'company_name')) {
                $table->dropIndex(['company_name']);
                $table->dropColumn('company_name');
            }
        });
    }
};
