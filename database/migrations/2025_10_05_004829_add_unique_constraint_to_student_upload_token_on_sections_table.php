<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUniqueConstraintToStudentUploadTokenOnSectionsTable extends Migration
{
    public function up()
    {
        // Drop the existing unique constraint if it exists
        Schema::table('sections', function (Blueprint $table) {
            // Drop the existing unique index on the student_upload_token column
            $table->dropUnique('sections_student_upload_token_unique');
        });

        // Add the unique constraint to the student_upload_token column
        Schema::table('sections', function (Blueprint $table) {
            $table->string('student_upload_token', 100)->unique()->change();
        });
    }

    public function down()
    {
        // If we need to roll back, remove the unique constraint
        Schema::table('sections', function (Blueprint $table) {
            $table->dropUnique('sections_student_upload_token_unique');
        });
    }
}
