<?php
//See https://medium.com/@codingcave/refactoring-data-in-a-production-laravel-application-b66526ae386
// php artisan db:refactor --class="AssignmentsTableRefactor"
use App\Assignment;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

class AssignmentsTableRefactor
{
    /**
     * Run the database refactoring.
     *
     * @return void
     */

    protected $count = 0;

    //This refactor is to move the information held in the AssignmentVersion model into the
    //Assignment model so that the AssignmentVersion can be deprecated.

    //Refactor starts by deleting old assignmentVersions (and their children questions) that are polluting the database.
    public function run()
    {

        //Add foreign key constraint so questions get deleted alongside AssignmentVersion
        Schema::table('questions', function (Blueprint $table) {
            $table->foreign('assignment_id')
                ->references('id')->on('assignment_versions')
                ->onDelete('cascade');
        });

        \Log::info("Starting assignment table refactor. " .
            Assignment::count() . " records to process.\n" .
            App\Question::count() . " questions.\n"
        );

        echo(Assignment::count() . "records to process.\n");
        Assignment::with(['version','version'])->chunk(100, function($assignments) {
            foreach($assignments as $assignment) {
                $this->count++;
                if($this->count%100==0)
                    echo("Processing record $this->count \n");
                //Start by deleting any version that is not the newest.  This should delete children questions and answers.

                for($i=0; $i<(count($assignment->versions)-1); $i++) {
                    $assignment->versions[$i]->delete();
                }

                //Somehow, a few assignments are missing a version entirely.  Delete them.
                if($assignment->version == null) {
                    Log::debug('Assignment ' . $assignment->id . 'in course ' . $assignment->course_id . ' missing version.  Deleting assignment');
                    $assignment->delete();
                    continue;
                }

                //Update the assignment model.
                $assignment->update([
                    'name' => $assignment->version->name,
                    'description' => $assignment->version->description,
                    'info' => $assignment->version->info,
                    'options' => $assignment->version->options,
                    ]);
            }
        });

        \Log::info("Finishing assignment table refactor. " .
            Assignment::count() . " assignments.\n" .
            App\Question::count() . " questions.\n"
        );

        //Drop the foreign key constraint to AssignmentVersion
        Schema::table('questions', function (Blueprint $table) {
            $table->dropForeign('questions_assignment_id_foreign');
        });
    }
}
