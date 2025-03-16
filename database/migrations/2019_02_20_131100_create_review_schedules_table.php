<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateReviewSchedulesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('review_schedules', function (Blueprint $table) {
            $table->increments('id');
            $table->dateTime('time');
            $table->integer('review_assignment_id')->unsigned();
            $table->integer('state');
            $table->integer('reviewNum')->nullable();
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
        Schema::dropIfExists('review_schedules');
    }
}
