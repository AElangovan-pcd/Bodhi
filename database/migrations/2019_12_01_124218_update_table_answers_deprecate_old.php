<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateTableAnswersDeprecateOld extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('answers', function (Blueprint $table) {
            $table->integer('attempts')->nullable()->change();
            $table->float('earned')->nullable()->change();
            $table->integer('possible')->nullable()->change();
            $table->longText('submission')->nullable()->change();
            $table->integer('assignment_id')->unsigned()->nullable()->change();
            $table->integer('variable_id')->unsigned()->nullable()->change(); // Reference variable
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('answers', function (Blueprint $table) {
            //
        });
    }
}
