<?php
//See https://medium.com/@codingcave/refactoring-data-in-a-production-laravel-application-b66526ae386
// php artisan db:refactor --class="QuestionsTableRefactor"
use App\Question;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

class QuestionsTableRefactor
{
    /**
     * Run the database refactoring.
     *
     * @return void
     */

    protected $count = 0;

    //This refactor is to change the assignment_id to refer to the Assignment model rather than to the
    //AssignmentVersion model (which is being deprecated).
    public function run()
    {

        \Log::info("Starting questions table refactor.\n "
            . App\Question::count() . " questions.\n"
        );

        //Kill the few standard questions that somehow don't have conditions.
        Question::where('type',1)->doesnthave('conditions')->delete();

        echo(Question::count() . " records to process.\n");
        Question::with(['assignmentVersion','conditions'])->chunk(100, function($questions) {
            foreach($questions as $question) {
                $this->count++;
                if($this->count%100==0)
                    echo("Processing record $this->count \n");
                if($question->type == 1)  //If it's a standard question, set the maximum points based on the conditions in addition to updating assignment_id
                    $question->update(['assignment_id' => $question->assignmentVersion->assignment_id, 'max_points' => collect($question->conditions)->max('points')]);
                else
                    $question->update(['assignment_id' => $question->assignmentVersion->assignment_id]);
            }
        });

        \Log::info("Finishing questions table refactor.\n "
            . App\Question::count() . " questions.\n"

        );

        //Add foreign key constraint for questions to assignment model
        Schema::table('questions', function (Blueprint $table) {
            $table->foreign('assignment_id')
                ->references('id')->on('assignments')
                ->onDelete('cascade');
        });
    }
}


