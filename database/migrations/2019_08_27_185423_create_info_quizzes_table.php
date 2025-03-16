<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateInfoQuizzesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('info_quizzes', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('info_id')->unsigned();
            $table->boolean('visible')->default(1);
            $table->dateTime('closed')->nullable();
            $table->integer('state')->nullable();
            $table->text('description')->nullable();
            $table->integer('order')->unsigned()->nullable();
            $table->text('options')->nullable();
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
        Schema::dropIfExists('info_quizzes');
    }
}
