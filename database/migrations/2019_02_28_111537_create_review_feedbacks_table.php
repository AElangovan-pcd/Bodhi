<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateReviewFeedbacksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('review_feedbacks', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('review_assignment_id')->unsigned();
            $table->integer('user_id')->unsigned();
            $table->string('filename');
            $table->string('extension');
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
        Schema::dropIfExists('review_feedbacks');
    }
}
