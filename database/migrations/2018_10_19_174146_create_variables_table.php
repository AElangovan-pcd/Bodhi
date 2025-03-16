<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateVariablesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('variables', function (Blueprint $table) {
            $table->increments('id');
            $table->string('type'); // 0: number, 1: array (column), 2: string
            $table->string('name');
            $table->string('title');
            $table->text('descript');
            $table->float('value')->default(0);
            $table->string('string')->default("");
            $table->integer('question_id')->unsigned();
            $table->boolean('active')->default(false);
            $table->boolean('shared')->default(false);
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
        Schema::dropIfExists('variables');
    }
}
