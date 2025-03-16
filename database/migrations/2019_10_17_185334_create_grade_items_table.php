<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGradeItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('grade_items', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('grade_group_id')->unsigned();
            $table->string('title');
            $table->text('comments')->nullable();
            $table->boolean('visible')->default(1);
            $table->integer('order')->nullable();
            $table->text('options')->nullable();
            $table->float('possible');
            $table->float('weight')->nullable();
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
        Schema::dropIfExists('grade_items');
    }
}
