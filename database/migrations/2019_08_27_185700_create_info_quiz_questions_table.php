<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateInfoQuizQuestionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('info_quiz_questions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('info_quiz_id')->unsigned();
            $table->integer('type')->unsigned();
            $table->longText('description')->nullable();
            $table->text('choices')->nullable();
            $table->text('options')->nullable();
            $table->text('answer')->nullable();
            $table->double('points')->nullable();
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
        Schema::dropIfExists('info_quiz_questions');
    }
}
