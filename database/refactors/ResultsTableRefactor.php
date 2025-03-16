<?php
//See https://medium.com/@codingcave/refactoring-data-in-a-production-laravel-application-b66526ae386
// php artisan db:refactor --class="ResultsTableRefactor"
use App\Question;
use App\Result;
use App\Score;
use App\Answer;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

class ResultsTableRefactor
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

        \Log::info("Starting results table refactor.\n "
            . App\Question::count() . " questions.\n"
            . App\Answer::count() . " answers.\n"
            . App\WrittenAnswerSubmission::count() . "written answers.\n"
        );

        echo(Question::count() . " records to process.\n");
        Question::with(['answers','wanswerSubmissions','scores.condition'])->chunk(100, function($questions) {
            foreach($questions as $question) {
                $this->count++;
                if($this->count%100==0) {
                    echo("Processing record $this->count \n");
                    \Log::info("Refactor record ". $this->count);
                }

                if($question->type == 1) {//Standard
                    foreach($question->answers->unique('user_id') as $ans) {
                        Result::updateOrcreate(
                            ['question_id' => $question->id,
                                'user_id' => $ans->user_id
                            ],
                            [
                                'earned' => $ans->earned,
                                'attempts' => $ans->attempts + 1,
                                'feedback' => $question->scores->where('user_id',$ans->user_id)->first()->condition->result,
                                'created_at' => $ans->created_at,
                                'updated_at' => $ans->updated_at,
                            ]
                        );
                    }

                }
                elseif($question->type == 2) { //Written
                    foreach($question->wanswerSubmissions->unique('user_id') as $ans) {
                        Answer::updateOrCreate(
                            ['user_id' =>  $ans->user_id,
                                //'assignment_id' => $question->assignment_id,
                                'question_id' => $question->id,
                            ],
                            ['variable_id' => 0,
                                "submission" => $ans->submission,
                                "created_at" => $ans->created_at,
                                "updated_at" => $ans->updated_at,
                                ]
                        );
                        Result::updateOrcreate(
                            ['question_id' => $question->id,
                                'user_id' => $ans->user_id
                            ],
                            [
                                'earned' => $ans->score,
                                'attempts' => 1,
                                'feedback' => $ans->response,
                                'created_at' => $ans->created_at,
                                'updated_at' => $ans->updated_at,
                                'status' => $ans->retry == 1 ? 2 : $ans->graded,
                            ]
                        );
                    }
                }
                elseif($question->type == 4) //Info Block
                    continue;
                else {  //Simple and Molecule Questions
                    foreach($question->answers->unique('user_id') as $ans) {
                        Result::updateOrcreate(
                            ['question_id' => $question->id,
                                'user_id' => $ans->user_id
                            ],
                            [
                                'earned' => $ans->earned,
                                'attempts' => $ans->attempts + 1,
                                'feedback' => $ans->earned == $ans->possible ? 'Correct!' : $question->feedback,
                                'created_at' => $ans->created_at,
                                'updated_at' => $ans->updated_at,
                            ]
                        );
                    }
                }
            }
        });

        \Log::info("Finishing results table refactor.\n "
            . App\Question::count() . " questions.\n"
            . App\Answer::count() . " answers.\n"
            . App\Result::count() . "results.\n"
        );

        //Add foreign key constraint for questions to assignment model
        Schema::table('questions', function (Blueprint $table) {
            $table->foreign('assignment_id')
                ->references('id')->on('assignments')
                ->onDelete('cascade');
        });
    }
}
