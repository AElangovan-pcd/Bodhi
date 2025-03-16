<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateReviewAssignmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('review_assignments', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('course_id')->unsigned();
            $table->integer('creator_id')->unsigned();
            $table->integer('type')->unsigned()->default(1);
            $table->string('name');
            $table->text('instructions')->nullable();
            $table->text('info')->nullable();
            $table->integer('reviewNum')->unsigned()->default(3);
            $table->integer('state')->default(0);
            $table->boolean('response')->default(false);
            $table->text('options')->nullable();
            $table->integer('order')->unsigned()->nullable();
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
        Schema::dropIfExists('review_assignments');
    }
}
