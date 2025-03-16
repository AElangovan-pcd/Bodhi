<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ModifyQuestionDependentsAddParents extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('variables', function (Blueprint $table) {
            $table->integer('parent_id')->nullable();
        });
        Schema::table('conditions', function (Blueprint $table) {
            $table->integer('parent_id')->nullable();
        });
        Schema::table('inter_variables', function (Blueprint $table) {
            $table->integer('parent_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('variables', function (Blueprint $table) {
            $table->dropColumn('parent_id');
        });
        Schema::table('conditions', function (Blueprint $table) {
            $table->dropColumn('parent_id');
        });
        Schema::table('inter_variables', function (Blueprint $table) {
            $table->dropColumn('parent_id');
        });
    }
}
