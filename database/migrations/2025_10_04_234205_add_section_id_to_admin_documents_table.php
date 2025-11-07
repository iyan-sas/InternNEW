<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSectionIdToAdminDocumentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('admin_documents', function (Blueprint $table) {
            $table->unsignedBigInteger('section_id')->nullable(); // Add the section_id column
            $table->foreign('section_id')->references('id')->on('sections')->onDelete('cascade'); // Add the foreign key constraint
            $table->string('student_upload_token', 100)->nullable(); // Token to store student upload link
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('admin_documents', function (Blueprint $table) {
            $table->dropForeign(['section_id']);
            $table->dropColumn('section_id');
        });
    }
}
