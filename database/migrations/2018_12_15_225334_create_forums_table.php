<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateForumsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('forums', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('course_id')->unsigned();
            $table->integer('user_id')->unsigned();
            $table->integer('type')->unsigned();
            $table->string('title',2000);
            $table->longText('question');
            $table->string('preview', 300);
            $table->text('tags');
            //$table->text('subscribers')->nullable();
            $table->boolean('anonymous');
            $table->boolean('solved')->default(false);
            //$table->integer('student_answers')->unsigned();
            //$table->boolean('instructor_answer');
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
        Schema::dropIfExists('forums');
    }
}
