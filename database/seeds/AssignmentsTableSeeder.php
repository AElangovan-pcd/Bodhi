<?php

use Illuminate\Database\Seeder;
use App\Assignment;
use App\AssignmentVersion;
use App\Question;
use App\Condition;
use App\Variable;
use App\InterVariable;

class AssignmentsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $as = new Assignment;
        $as->course_id = 1;
        $as->creator_id = 1;
        $as->active = 1;
        $as->order = 1;
        $as->name = "Multiple Test";
        $as->save();

        // Question 1
        $q = new Question;
        $q->name = "Primary Calculations";
        $q->description = "The answer here is b=c, where c=2*a";
        $q->assignment_id = $as->id;
        $q->order = 1;
        $q->type = 1;
        $q->max_points = 1;
        $q->save();

        // Conditions for Q1
        $con = new Condition;
        $con->equation = "b = c";
        $con->result = "Correct!";
        $con->question_id = $q->id;
        $con->points = 1;
        $con->type = 1;
        $con->save();

        $con = new Condition;
        $con->equation = "1";
        $con->result = "Incorrect";
        $con->question_id = $q->id;
        $con->points = 0;
        $con->type = 1;
        $con->save();

        // Variables for Q1
        $var = new Variable;
        $var->name = "a";
        $var->title = "a";
        $var->descript = "a";
        $var->type = 0;
        $var->question_id = $q->id;
        $var->shared = true;
        $var->save();

        $var = new Variable;
        $var->name = "b";
        $var->title = "b";
        $var->descript = "b";
        $var->type = 0;
        $var->question_id = $q->id;
        $var->shared = false;
        $var->save();

        $var = new InterVariable;
        $var->name = "c";
        $var->question_id = $q->id;
        $var->equation = "2*a";
        $var->save();

        // Question 2
        $q = new Question;
        $q->name = "Secondary Calculations";
        $q->description = "The answer here is f=e where e=c+d";
        $q->assignment_id = $as->id;
        $q->order = 1;
        $q->type = 1;
        $q->max_points = 1;
        $q->save();

        // Conditions for Q1
        $con = new Condition;
        $con->equation = "f=e";
        $con->result = "Correct!";
        $con->question_id = $q->id;
        $con->points = 1;
        $con->type = 1;
        $con->save();

        $con = new Condition;
        $con->equation = "1";
        $con->result = "Incorrect";
        $con->question_id = $q->id;
        $con->points = 0;
        $con->type = 1;
        $con->save();

        $var = new Variable;
        $var->name = "d";
        $var->title = "d";
        $var->descript = "d";
        $var->type = 0;
        $var->question_id = $q->id;
        $var->shared = false;
        $var->save();

        $var = new Variable;
        $var->name = "f";
        $var->title = "f";
        $var->descript = "f";
        $var->type = 0;
        $var->question_id = $q->id;
        $var->shared = false;
        $var->save();

        $var = new InterVariable;
        $var->name = "e";
        $var->question_id = $q->id;
        $var->equation = "c+d";
        $var->save();
    }
}
