<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateReviewRevisionSubmissionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('review_revision_submissions', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('review_assignment_id')->unsigned();
            $table->integer('user_id')->unsigned();
            $table->string('filename')->nullable();
            $table->string('extension')->nullable();
            $table->string('response_filename')->nullable();
            $table->string('response_extension')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('review_revision_submissions');
    }
}
