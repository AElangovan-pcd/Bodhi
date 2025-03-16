<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateQuizJobsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('quiz_jobs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('assignment_id');
            $table->unsignedInteger('user_id');
            $table->dateTime('allowed_start');
            $table->dateTime('allowed_end')->nullable();
            $table->dateTime('actual_start')->nullable();
            $table->unsignedInteger('allowed_minutes');
            $table->float('elapsed_time')->nullable();
            $table->text('options')->nullable();
            $table->integer('status');
            $table->text('question_list');
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
        Schema::dropIfExists('quiz_jobs');
    }
}
