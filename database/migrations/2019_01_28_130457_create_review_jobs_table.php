<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateReviewJobsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('review_jobs', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('review_assignment_id')->unsigned();
            $table->integer('user_id')->unsigned();
            $table->integer('review_submission_id')->unsigned();
            $table->boolean('complete')->default(false);
            $table->boolean('viewed')->default(false);
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
        Schema::dropIfExists('review_jobs');
    }
}
