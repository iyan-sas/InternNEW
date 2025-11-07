<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('student_verifications', function (Blueprint $table) {
            $table->string('cor_file')->nullable()->change();
            $table->string('id_file')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('student_verifications', function (Blueprint $table) {
            $table->string('cor_file')->nullable(false)->change();
            $table->string('id_file')->nullable(false)->change();
        });
    }
};
