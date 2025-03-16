<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateQuestionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('questions', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('description', 2000)->nullable();
            $table->integer('type')->default(1); // 1: standard question, 2: Short answer question, 3: simple question
            $table->integer('assignment_id')->unsigned();
            $table->integer('max_points')->unsigned()->default(0);
            $table->integer('order')->unsigned();
            $table->string('answer')->nullable();
            $table->decimal('tolerance')->default(0);
            $table->integer('tolerance_type')->default(0); // 0: Percent, 1: Range
            $table->text('feedback')->nullable();	// for simple questions
            $table->boolean('active')->default(0);
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
        Schema::dropIfExists('questions');
    }
}
