<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddForeignKeysToQuestionsAndChildren extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //Clean up orphans in the database:
        \App\Score::doesnthave('question')->delete();
        \App\Result::doesnthave('question')->delete();
        \App\Condition::doesnthave('question')->delete();
        \App\InterVariable::doesnthave('question')->delete();
        \App\Variable::doesnthave('question')->delete();
        \App\ProfResponse::doesnthave('question')->delete();
        \App\WrittenAnswerSubmission::doesnthave('question')->delete();
        \App\Comment::doesnthave('question')->delete();

        Schema::table('questions', function (Blueprint $table) {
            $table->foreign('assignment_id')
                ->references('id')->on('assignments')
                ->onDelete('cascade');
        });
        Schema::table('scores', function (Blueprint $table) {
            $table->foreign('question_id')
                ->references('id')->on('questions')
                ->onDelete('cascade');
        });
        Schema::table('results', function (Blueprint $table) {
            $table->foreign('question_id')
                ->references('id')->on('questions')
                ->onDelete('cascade');
        });
        Schema::table('conditions', function (Blueprint $table) {
            $table->foreign('question_id')
                ->references('id')->on('questions')
                ->onDelete('cascade');
        });
        Schema::table('inter_variables', function (Blueprint $table) {
            $table->foreign('question_id')
                ->references('id')->on('questions')
                ->onDelete('cascade');
        });
        Schema::table('variables', function (Blueprint $table) {
            $table->foreign('question_id')
                ->references('id')->on('questions')
                ->onDelete('cascade');
        });
        Schema::table('prof_responses', function (Blueprint $table) {
            $table->foreign('question_id')
                ->references('id')->on('questions')
                ->onDelete('cascade');
        });
        Schema::table('written_answer_submissions', function (Blueprint $table) {
            $table->foreign('question_id')
                ->references('id')->on('questions')
                ->onDelete('cascade');
        });
        Schema::table('comments', function (Blueprint $table) {
            $table->foreign('question_id')
                ->references('id')->on('questions')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('questions', function (Blueprint $table) {
            $table->dropForeign('questions_assignment_id_foreign');
        });
        Schema::table('scores', function (Blueprint $table) {
            $table->dropForeign('scores_question_id_foreign');
        });
        Schema::table('results', function (Blueprint $table) {
            $table->dropForeign('results_question_id_foreign');
        });
        Schema::table('conditions', function (Blueprint $table) {
            $table->dropForeign('conditions_question_id_foreign');
        });
        Schema::table('inter_variables', function (Blueprint $table) {
            $table->dropForeign('inter_variables_question_id_foreign');
        });
        Schema::table('variables', function (Blueprint $table) {
            $table->dropForeign('variables_question_id_foreign');
        });
        Schema::table('prof_responses', function (Blueprint $table) {
            $table->dropForeign('prof_responses_question_id_foreign');
        });
        Schema::table('written_answer_submissions', function (Blueprint $table) {
            $table->dropForeign('written_answer_submissions_question_id_foreign');
        });
        Schema::table('comments', function (Blueprint $table) {
            $table->dropForeign('comments_question_id_foreign');
        });
    }
}
