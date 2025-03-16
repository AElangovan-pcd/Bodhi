<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Library\Evaluator;
use App\Library\EvaluateMethods;
use Exception;
use Auth;

class Question extends Model
{
    const DECIMAL_VARIABLE = 0;
    const COLUMN_VARIABLE = 1;
    const STRING_VARIABLE = 2;
    const COMPUTED_VARIABLE = 3;
    const SELECTION_VARIABLE = 4;

    const STANDARD_QUESTION = 1;
    const SHORT_ANSWER_QUESTION = 2;
    const SIMPLE_QUESTION = 3;
    const UNANSWERED_QUESTION = 4;
    const SIMPLE_TEXT_QUESTION = 5;
    const MOLECULE_QUESTION = 6;
    const MULTIPLE_CHOICE_QUESTION = 7;
    const REACTION_QUESTION = 8;

    const PERCENT = 0;
    const RANGE = 1;

    const CASE_INSENSITIVE = 0;
    const CASE_SENSITIVE = 1;

    protected $fillable = ['assignment_id','max_points','options'];

    protected $casts = ['extra' => 'array', 'molecule' => 'array', 'options' => 'array', 'choices' => 'array'];

    public function getAnswerAttribute($value) {
        if($this->type == self::REACTION_QUESTION)
            return json_decode($value);
        return $value;
    }

    /*public function setAnswerAttribute($value) {
        if($this->type == self::REACTION_QUESTION) {
            $this->answer = json_encode($value);
            \Debugbar::alert($this->answer);
        }
    }*/

    public function assignment() {
        return $this->belongsTo('App\Assignment');
    }

    public function linked_questions() {
        return $this->hasMany('App\Question', 'parent_question_id');
    }

    public function answers() {
        return $this->hasMany('App\Answer');
    }

    public function answer() {
        return $this->hasOne('App\Answer')->latest();
    }

    public function conditions() {
        return $this->hasMany('App\Condition')->orderBy('order');
    }

    public function variables() {
        return $this->hasMany('App\Variable')->orderBy('order');
    }

    public function computedVariables() {
        return $this->hasMany('App\Variable')->orderBy('order');
    }

    public function results() {
        return $this->hasMany('App\Result');
    }

    public function result() {
        return $this->hasOne('App\Result')->latest();
    }

    public function responses() {
        return $this->hasMany('App\ProfResponse')->orderBy('order');
    }

    public function interVariables() {
        return $this->hasMany('App\InterVariable')->orderBy('order');
    }

    public function comments() {
        return $this->hasMany('App\Comment');
    }

    public function inject_computed($computedVars) {
        $this->description = $this->replace_computed($this->description, $computedVars);
        if(isset($this->extra['text'])) {
            $extra = $this->extra;
            $extra['text'] = $this->replace_computed($extra['text'], $computedVars);
            $this->extra = $extra;
        }
        foreach($this->variables as $variable) {
            if($variable->type == self::SELECTION_VARIABLE) {
                $choices = $variable->choices;
                foreach($choices as $key => $choice)
                    $choices[$key] = $this->replace_computed($choice, $computedVars);
                $variable->choices = $choices;
            }
        }
    }

    private function replace_computed($field, $computedVars) {
        \Debugbar::info($field);
        \Debugbar::debug($computedVars);
        return preg_replace_callback_array(
            [
                '/##(\S+?)##/' => function($matches) use($computedVars) {
                    return $this->format_computed($this->get_computed($matches[1], $computedVars), "auto");
                },
                '/#_(\S+?)_#/' => function($matches) use($computedVars) {
                    return $this->format_computed($this->get_computed($matches[1], $computedVars), "decimal");
                },
                '/#\^(\S+?)\^#/' => function($matches) use($computedVars) {
                    return $this->format_computed($this->get_computed($matches[1], $computedVars), "scientific");
                }
            ],
            $field);
    }

    private function get_computed($name, $computedVars) {
        try {
            return $computedVars[$name];
        }
        catch(Exception $e) {
            return $name;
        }
    }

    public function new_computed_values_for_user($user_id) {
        $cache = $this->assignment->get_user_cache($user_id);
        if(!isset($cache))
            return ['status' => 'error', 'msg' => 'User has not yet loaded assignment. Cannot refresh.'];
        $cachedVals = json_decode($cache->values, true);
        foreach ($this->variables as $key => $v) {
            if ($v->type == self::COMPUTED_VARIABLE) {
                $variableName = json_decode(json_encode($v->name));
                if(($this->options["isolated"] ?? false))
                    $variableName = $variableName . "__" . $this->id;
                try {
                    $eval = new Evaluator($cachedVals);
                    $cachedVals[$variableName] = $eval->getValue($v->descript);
                }
                catch(\Throwable $e) {
                    report($e);
                    $cachedVals[$variableName] = "<b>error</b>";
                }
            }
        }
        $cache->values = json_encode($cachedVals,JSON_PARTIAL_OUTPUT_ON_ERROR);
        $cache->save();
        return ['status' => 'success', 'msg' => 'New computed values generated.'];
    }

    // Formats:
    // "auto" formats to scientific notation if greater than 10^2 or less than 10^-2
    // "scientific" always formats to scientific notation
    // "decimal" always formats to decimal
    // when $E is true, use E notation instead of scientific notation
    private function format_computed($val, $format, $E = false) {
        // \Debugbar::debug($val, $format);
        if(is_array($val))
            return $this->format_array($val,$format);
        try {
            if(!is_numeric($val)) {
                return $val;
            }
            if ($val == 0)
                return 0;
            $order = floor(log10(abs($val)));
            if ($format == "scientific" || ($format == "auto" && ($order < -2 || $order > 2))) {
                $sign = $val > 0 ? 1 : -1;
                $mantissa = $sign * (abs($val) / pow(10, $order));
                $exp = $E ?  'E' . $order : ' x 10' . '<sup>' . $order . '</sup>';
                return EvaluateMethods::sigs($mantissa, EvaluateMethods::sigfigs($val)) . $exp;
            }

            //Implicitly decimal; this will also insert thousands separators due to the way the sigs function works.
            return EvaluateMethods::sigs($val, EvaluateMethods::sigfigs($val));
        }
        catch(Exception $e) {
            return $val;
        }
    }

    private function format_array($val, $format) {
        $string = "<textarea rows=\"5\" class='d-inline input form-control array_input'\n" .
            "                          style='width: 7em; resize:both' readonly='readonly'>";
        foreach($val as $v) {
            $string .= $this->format_computed($v, $format, true) . "\n";
        }
        $string .= "</textarea>";
        return $string;
    }

    public function shuffle_MC() {
        $choices = $this->choices;
        $options = $this->options;

        //Pull out the locked items
        $locked = [];
        foreach($choices as $i => $choice)
            if(isset($choice['locked']) && $choice['locked']) {
                $locked[$i] = $choice;
            }

        //Remove locked items from the array
        $choices = array_filter($choices, function($choice) {
            if(isset($choice['locked']) && $choice['locked'])
                return false;
            return true;
        });

        //Shuffle
        if($options['MC']['shuffleType'] == 'random')
            shuffle($choices);
        if($options['MC']['shuffleType'] == 'reverse' && (bool)random_int(0, 1))
            $choices = array_reverse($choices);

        //Reindex the array
        $choices = array_values($choices);

        //Insert locked items
        foreach($locked as $i => $item) {
            array_splice($choices, $i, 0, [$item]);
        }
        return $choices;
    }

    public function shuffle_MC_apply($sortOrder) {
        $choices = $this->choices;
        //Index the choices by id
        $choices = array_combine(
            array_column($choices, 'id'),
            $choices
        );
        //Order the choices based on $sortOrder
        $choices = array_map(function($id) use($choices) {
            return $choices[$id];
        }, $sortOrder);

        return $choices;
    }

    public function generate_linked_question($aid) {
        $linked = $this->replicate();
        $linked->parent_question_id = $this->id;
        $linked->assignment_id = $aid;
        $linked->save();
        $this->duplicate_question_pieces($linked->id);
        return $linked->id;
    }

    public function duplicate_question_pieces($new_question_id) {
        // Copy Conditions
        foreach ($this->conditions as $condition)
        {
            $new_condition = $condition->replicate();
            $new_condition->question_id = $new_question_id;
            $new_condition->parent_id = $condition->id;
            $new_condition->save();
        }
        // Copy Intermediate Variables
        foreach ($this->interVariables as $interVar)
        {
            $new_inter = $interVar->replicate();
            $new_inter->question_id = $new_question_id;
            $new_inter->parent_id = $interVar->id;
            $new_inter->save();
        }
        // Copy Variables
        foreach ($this->variables as $variable)
        {
            $new_variable = $variable->replicate();
            $new_variable->question_id = $new_question_id;
            $new_variable->parent_id = $variable->id;
            $new_variable->save();
        }
        // if ($question->type == 2){
        foreach ($this->responses as $resp) {
            $new_resp = $resp->replicate();
            $new_resp->question_id = $new_question_id;
            $new_resp->save();
        }
    }

    /*------------------------------------------------------------------------
    * Question Progress/Scoring Functions
    *------------------------------------------------------------------------
    */

    public function processIntermediates($data)
    {
        $inters = $this->interVariables;
        foreach ($inters as $i)
        {
            try
            {
                $eval = new Evaluator($data);
                $data[$i->name] = $eval->getValue($i->equation);
            }
            catch (Exception $e)
            {
                $e->equation = $i->equation;
                throw $e;
            }
        }
        return $data;
    }

    public function evaluate($data, $user = null, $rescore = false, $preview = false)
    {
        if($user == null)
            $user = Auth::user();
        if(!$rescore && !$preview && $this->assignment->type == 2) {
            $allow = $this->assignment->check_quiz($user);
            if(!$allow['allow'])
                return array("output" => $allow['message'], "valid" => false, "question" => $this->id);
        }

        if ($this->type == self::STANDARD_QUESTION)
            $eval = $this->evaluateStandard($data, $user, $rescore, $preview);
        else if ($this->type == self::SIMPLE_QUESTION || $this->type == self::SIMPLE_TEXT_QUESTION)
            $eval = $this->evaluateSimple($data, $user, $rescore);
        else if ($this->type == self::MULTIPLE_CHOICE_QUESTION)
            $eval = $this->evaluateMultipleChoice($data, $user, $rescore);
        else if ($this->type == self::REACTION_QUESTION)
            $eval = $this->evaluateReaction($data, $user, $rescore);

        if($this->check_deferred() && !$preview) {
            if($eval['valid'])  //Still return output about errors.
                $eval['output'] = "Submission recorded.";
            $eval['earned'] = null;
        }
        return $eval;
    }

    private function check_deferred() {
        if($this->assignment->type == 2)  //Quiz
            return true;
        if($this->deferred && !(isset($this->assignment->options['deferredOverride']) && $this->assignment->options['deferredOverride']))
            return true;
        return false;
    }

    private function evaluateStandard($variables, $user, $rescore = false, $preview = false)
    {
        $conditions = $this->conditions;

        foreach($conditions as $con)
        {
            $output = $con->evaluate($variables);
            if ($output !== false)
            {
                \Debugbar::addMessage('Evaluate standard');
                \Debugbar::info($output);
                \Debugbar::info($variables);

                $answer = $this->save_standard_answers($output, $user);
                $result = $this->save_results($output, $user, $rescore);
                $output['attempts'] = $result["attempts"];
                $output['question'] = $this->id;

                if($this->check_deferred() && !$preview) {
                    if($output['valid'])  //Still return output about errors.
                        $output['output'] = "Submission recorded.";
                }
                $output = $this->check_saved($output, $answer, $result);
                return $output;
            }
        }

        return array("output" => "No result found", "valid" => false, "question" => $this->id);
    }

    public function evaluateMolecule($submission, $comparison, $numMatches, $groups, $feedback, $user = null, $preview = false) {
        if($user == null)
            $user = Auth::user();
        if(!$preview && $this->assignment->type == 2) {
            $allow = $this->assignment->check_quiz($user);
            if(!$allow['allow'])
                return array("output" => $allow['message'], "valid" => false, "question" => $this->id);
        }

        $output = array();
        if ($comparison=='true')
        {
            $output["output"] = $feedback;
            $output["valid"] = true;
            $output["earned"] = $this->max_points;
        }
        else
        {
            $output["output"] = "Incorrect: $feedback";
            $output["valid"] = true;
            $output["earned"] = 0;

            if($this->molecule['evalType'] == 'formula')
                $output["earned"] = $this->max_points * $this->moleculeRequirements($numMatches, $groups);

        }

        $answer = $this->save_simple_answer($submission, $user);
        $result = $this->save_results($output, $user);
        $attempts = $result["attempts"];

        if($this->check_deferred() && !$preview) {
            if($output['valid'])  //Still return output about errors.
                $output['output'] = "Submission recorded.";
            $output['earned'] = null;
        }

        $output = $this->check_saved($output, $answer, $result);

        return array(
            "output"    => $output['output'],
            "valid"     => $output['valid'],
            "values" => array(),
            "earned"     => $output["earned"],
            "question"  => $this->id,
            "attempts"  => $attempts,
        );
    }

    private function moleculeRequirements($numMatches, $groups) {

        if($this->molecule['evalType'] != 'formula')
            return 0;

        $met = $numMatches;
        $reqs = $this->molecule['structureNum'];
        \Debugbar::debug($groups);
        if($this->molecule['groupMatchType'] == 'any') {
            $reqs += 1;
            if($groups['groupMatches'] > 0)
                $met += 1;
        }
        if($this->molecule['groupMatchType'] == 'all' || $this->molecule['groupMatchType'] == 'each/all' || $this->molecule['groupMatchType'] == 'each/all') {
            $groupNum = count($this->molecule['groups']);
            $reqs += $groupNum;
            $met += $groups['groupsMatched'];
        }
        /*if($this->molecule['groupMatchType'] == 'each' || $this->molecule['groupMatchType'] == 'each/all') {
            $met -= $groups['groupFails'];
        }*/

        $fraction = round($met/$reqs,2);
        $fraction = $fraction > 1 ? 1 : $fraction;

        return $fraction;
    }

    private function evaluateSimple($variables, $user, $rescore)
    {
        $assignment = $this->assignment;

        $value = $variables[$this->id];
        $center = trim($this->answer);
        $tolerance = $this->tolerance;
        $feedback = $this->feedback;

        if($this->type == self::SIMPLE_QUESTION) {
            if ($this->tolerance_type == self::RANGE)
                $r = EvaluateMethods::withinRange($value, $center, $tolerance);
            else
                $r = EvaluateMethods::withinPercent($value, $center, $tolerance);
        }
        else if($this->type == self::SIMPLE_TEXT_QUESTION) {
            $value = trim($value);
            $answers = explode(",",$center);
            $r = false;
            foreach($answers as $answer) {
                $answer = trim($answer);
                if($this->tolerance_type == self::CASE_INSENSITIVE)
                    $cmp = strcasecmp($value,$answer);
                else
                    $cmp = strcmp($value,$answer);

                if($cmp == 0) {
                    $r = true;
                    break;
                }
            }
        }

        $output = array();
        if ($r)
        {
            $output["output"] = "Correct!";
            $output["valid"] = true;
            $output["earned"] = $this->max_points;
        }
        else
        {
            $output["output"] = "Incorrect: $feedback";
            $output["valid"] = true;
            $output["earned"] = 0;
        }

        $answer = $this->save_simple_answer($value, $user);
        $result = $this->save_results($output, $user, $rescore);
        $attempts = $result["attempts"];

        $output = $this->check_saved($output, $answer, $result);

        return array(
            "output"    => $output['output'],
            "valid"     => $output['valid'],
            "values" => array(),
            "earned"     => $output["earned"],
            "question"  => $this->id,
            "attempts"  => $attempts,
        );
    }

    private function evaluateMultipleChoice($variables, $user, $rescore) {
        $assignment = $this->assignment;

        $input = $variables[$this->id];
        $submission = explode(',',$input);
        $answer = explode(',',$this->answer);
        $feedback = $this->feedback;

        $submission = $this->checkMCValidity($submission);

        if($this->options['MC']['type'] == 'multiple' && isset($this->options['MC']['fractional']) && $this->options['MC']['fractional'])
            $points = $this->evaluateFractionalMC($answer, $submission);
        else
            $points = $this->evaluateNormalMC($answer, $submission);

        $output["earned"] = $points;
        $output["valid"] = true;

        if ($points == $this->max_points)
            $output["output"] = "Correct!";
        else if ($points == 0)
            $output["output"] = "Incorrect: $feedback";
        else
            $output["output"] = "Partially correct: $feedback";

        $answer = $this->save_simple_answer($input, $user);
        $result = $this->save_results($output, $user, $rescore);
        $attempts = $result["attempts"];

        $output = $this->check_saved($output, $answer, $result);

        return array(
            "output"    => $output['output'],
            "valid"     => $output['valid'],
            "values" => array(),
            "earned"     => $output["earned"],
            "question"  => $this->id,
            "attempts"  => $attempts,
        );
    }

    //If the submission contains MC indices that no longer exist, don't evaluate them.  Still store the original submission, though.
    private function checkMCValidity($submission) {
        $choices = array_column($this->choices, "id");
        foreach($submission as $key => $sub) {
            if(!in_array($sub, $choices))
                unset($submission[$key]);
        }
        return $submission;
    }

    private function evaluateFractionalMC($answer, $submission) {
        $pts = 0;
        if(count($answer) == 0)
            return $this->evaluateNormalMC($answer, $submission);
        $fraction = $this->max_points / count($answer);
        foreach($submission as $sub) {
            if(in_array($sub, $answer))
                $pts += $fraction;
            else
                $pts -= $fraction;
        }
        $pts = $pts < 0 ? 0 : $pts;
        return round($pts,2);
    }

    private function evaluateNormalMC($answer, $submission) {
        if(count($answer) != count($submission))
            $correct = false;
        else {
            $correct = true;
            foreach($submission as $sub) {
                if(!in_array($sub, $answer))
                    $correct = false;
            }
        }
        if($correct)
            return $this->max_points;
        else
            return 0;
    }

    private function evaluateReaction($variables, $user, $rescore) {
        $assignment = $this->assignment;

        $input = $variables[$this->id];
        $submission = json_decode($input);
        $answer = $this->answer;
        $feedback = "Default feedback";

        //$output["earned"] = $points;
        $output["valid"] = true;

        $reaction = $this->evaluateReactionSpecies($submission, $answer);
        $output["earned"] = $reaction["points"];
        $output["output"] = $reaction["feedback"];

        $answer = $this->save_simple_answer($input, $user);
        $result = $this->save_results($output, $user, $rescore);
        $attempts = $result["attempts"];

        $output = $this->check_saved($output, $answer, $result);

        return array(
            "output"    => $output['output'],
            "valid"     => $output['valid'],
            "values" => array(),
            "earned"     => $output["earned"],
            "question"  => $this->id,
            "attempts"  => $attempts,
        );
    }

    private function evaluateReactionSpecies($submission, $answer) {

        $reactants = $this->getReactionFormulaMatches($submission->reactants ?? [], $answer->reactants ?? []);
        $products = $this->getReactionFormulaMatches($submission->products ?? [], $answer->products ?? []);
        $balance = $this->checkReactionBalance($reactants, $products);

        return ["feedback" => $this->getReactionFeedback($reactants, $products, $balance), "points" => $this->getReactionScore($reactants, $products, $balance)];
    }

    // Returns array of paired species from the expected answer and the submission along with the number of matched species.
    // Allows species to be in any order.
    private function getReactionFormulaMatches($sub, $ans) {
        $sub = $this->dropEmptyFormulas($sub);
        $ans = $this->dropEmptyFormulas($ans);

        $species = [];
        $matches = [
            "expected" => count($ans),
            "formulas" => 0,
            "charges" => 0,
            "phases" => 0,
        ];
        foreach($ans as $key => $a) {
            $species[$key]['answer'] = $a;
            foreach($sub as $s) {
                $checks = EvaluateMethods::checkChemicalFormula($s, $a);
                if($checks["formula"]) {
                    $species[$key]['submitted'] = $s;
                    $species[$key]['checks'] = $checks;
                    $matches["formulas"]++;
                    if($checks["charge"])
                        $matches["charges"]++;
                    if($checks["phase"])
                        $matches["phases"]++;
                    break;
                }
            }
        }
        $checks = $this->checkReactionFormulaMatches($species, $matches, count($sub), count($ans));
        return [
            "species" => $species,
            "matches" => $matches,
            "checks" => $checks,
            "complete" => $this->checkReactionSideCompletion($checks),
        ];
    }

    private function dropEmptyFormulas($arr) {
        $newArr = [];
        foreach($arr as $el) {
            if($el->formula !== null && $el->formula !=="" )
                $newArr[] = $el;
        }
        return $newArr;
    }

    private function checkReactionFormulaMatches($species, $matches, $subCount, $ansCount) {
        if($subCount != $ansCount)  //Number of submitted species not equal to number in the formula
            return ["formulas" => false];
        if(!($matches["formulas"] == $matches["expected"] && $matches["formulas"] == $subCount)) //Number of formulas entered equals expected number of matches
            return ["formulas" => false];
        $checks = ["formulas" => true, "charges" => true, "phases" => true];
        foreach($species as $s) {
            if(!$s["checks"]["charge"])
                $checks["charges"] = false;
            if(!$s["checks"]["phase"])
                $checks["phases"] = false;
        }
        return $checks;
    }

    private function checkReactionSideCompletion($checks) {
        $f = $checks["formulas"] ?? false;
        $c = $checks["charges"] ?? false;
        $p = $this->options["reaction"]["phase"] ? ($checks["phases"] ?? false) : true;
        return ($f && $c && $p);
    }

    private function checkReactionBalance($reactants, $products) {
        if(!$reactants["checks"]["formulas"] ||!$products["checks"]["formulas"])
            return false;
        $items = array_merge($reactants["species"], $products["species"]);

        $ans = [];
        $sub = [];
        // Get arrays of balancing coefficients for matched formulas from answer and submission
        foreach($items as $item) {
            $ans[] = $this->parseReactionCoefficient($item["answer"]);
            $sub[] = $this->parseReactionCoefficient($item["submitted"]);
        }
        // Get the ratios of balancing coefficients. e.g. 2 A + 3 B compared to 4 A + 6 B will give the ratios [2, 2]
        $ratios = $this->compareBalanceCoefficients($ans, $sub);
        // Filter down to get unique list of balancing coefficient ratios.
        // If the reactions have the same balance (even if multiplied by a constant), there should be a single ratio in the list.
        // If the reactions are balanced identically, this ratio should be 1.
        $unique = array_unique($ratios);

        $mode = $this->answer->balanceMode;
        if($mode == "exact") {
            return (count($unique) === 1 && $unique[0] == 1);
        }
        if($mode == "any") {
            return (count($unique) === 1);
        }
        if($mode == "ignore") {
            return true;
        }
    }

    private function compareBalanceCoefficients($ans, $sub) {
        return array_map(function ($a, $b) {return $a / $b;}, $ans, $sub);
    }

    private function parseReactionCoefficient($species) {
        $coef = $species->coefficient ?? 1;
        $coef = $coef == "" ? 1 : $coef;
        return $this->parseFraction($coef);
    }

    //Adapted from Tim Williams's answer at https://stackoverflow.com/a/58574477
    //CC BY-SA 4.0 https://creativecommons.org/licenses/by-sa/4.0/
    private function parseFraction(string $fraction): float
    {
        if(preg_match('#(\d+)\s+(\d+)/(\d+)#', $fraction, $m)) {
            return ($m[1] + $m[2] / $m[3]);
        } else if( preg_match('#(\d+)/(\d+)#', $fraction, $m) ) {
            return ($m[1] / $m[2]);
        }
        return (float)$fraction;
    }

    private function getReactionFeedback($reactants, $products, $balance) {
        $mode = $this->answer->feedbackMode;
        $correct = $this->answer->feedback->correct ?? 'Correct!';
        $incorrect = $this->answer->feedback->incorrect ?? 'Incorrect';

        if($mode == "simple") {
            if ((!$reactants["complete"] || !$products["complete"] || !$balance))
                return $incorrect;
            //Need to check for balance
            return $correct;
        }

        if($mode == "directed") {
            $fb = [];
            if(!$reactants["complete"]) {
                $fb[] = "Check your reactants.";
                $fb = $this->getReactionDirectedFeedback($fb, $reactants);
            }
            if(!$products["complete"]) {

                $fb[] = "Check your products.";
                $fb = $this->getReactionDirectedFeedback($fb, $products);
            }
            if($reactants["complete"] && $products["complete"] && !$balance)
                $fb[] = "Your reaction is not balanced correctly.";

            if($fb == [])
                $fb[] = $correct;
            else
                array_unshift($fb, $incorrect . ' ');
            return implode(" ", $fb);
        }
    }

    private function getReactionDirectedFeedback($fb, $species) {
        if(!$species["checks"]["formulas"]) {
            $fb[] = "You do not have the correct formulas.";
            return $fb;
        }

        if(!$species["checks"]["charges"])
            $fb[] = "You do not have the correct charges.";
        $phaseCheck = $this->options["reaction"]["phase"] ? $species["checks"]["phases"] : true;
        if(!$phaseCheck)
            $fb[] = "You do not have the correct phases.";
        return $fb;
    }

    private function getReactionScore($reactants, $products, $balance) {
        $mode = $this->answer->scoringMode;
        if($mode == "simple") {
            if (!$reactants["complete"] || !$products["complete"] || !$balance)
                return 0;
            return $this->max_points;
        }
        if($mode == "basic") {
            return $this->getBasicReactionScore($reactants, $products, $balance);
        }
        if($mode == "specified") {
            return $this->getSpecifiedReactionScore($reactants, $products, $balance);
        }
    }

    private function getBasicReactionScore($reactants, $products, $balance) {
        $score = 0;
        $frac = $this->options["reaction"]["phase"] ? (1/5) : (1/3);  // Fraction depends on whether phase is included.
        $score += $this->checkReactionSideFormulasAndCharges($reactants["checks"]) ? $frac : 0;
        $score += $this->checkReactionSideFormulasAndCharges($products["checks"]) ? $frac : 0;
        if($this->options["reaction"]["phase"]) {
            $score += ($reactants["checks"]["phases"] ?? false) ? $frac : 0;
            $score += ($products["checks"]["phases"] ?? false) ? $frac : 0;
        }
        $score += $balance ? $frac : 0;
        return round($score*$this->max_points,2);
    }

    private function getSpecifiedReactionScore($reactants, $products, $balance) {
        $score = 0;

        $score += $this->checkReactionSideFormulasAndCharges($reactants["checks"]) ? $this->parseFraction($this->answer->specified->points->reactant_formulas ?? 0) : 0;
        $score += $this->checkReactionSideFormulasAndCharges($products["checks"]) ? $this->parseFraction($this->answer->specified->points->product_formulas ?? 0) : 0;
        if($this->options["reaction"]["phase"]) {
            $score += ($reactants["checks"]["phases"] ?? false) ? $this->parseFraction($this->answer->specified->points->reactant_phases ?? 0) : 0;
            $score += ($products["checks"]["phases"] ?? false) ? $this->parseFraction($this->answer->specified->points->product_phases ?? 0) : 0;
        }
        $score += $balance ? $this->parseFraction($this->answer->specified->points->balance ?? 0) : 0;
        return round($score*$this->max_points,2);
    }

    private function checkReactionSideFormulasAndCharges($checks) {
        $f = $checks["formulas"] ?? false;
        $c = $checks["charges"] ?? false;
        return ($f and $c);
    }

    private function save_standard_answers($output, $user) {
        //Get existing answers for this question for this user
        //The has line is to avoid pulling answers where the variable has been deleted
        $answers = $this->answers()->where('user_id',$user->id)->has('variable')->with('variable')->get();
        //Make associative array of existing answers by variable name
        $ans_array_object = $answers->mapWithKeys(function ($ans) {
            return [$ans['variable']['name'] => $ans];
        });
        $ans_array = $ans_array_object->all();
        //Loop through the question variables and update answers or create new ones as appropriate
        $saved = true;
        //TODO If a race condition has created duplicate answers, only the older answer gets updated (and not the newer one).
        foreach ($this->variables as $v => $var)
        {
            if(array_key_exists($var->name, $ans_array)) {
                $answer = $this->update_standard_answer($ans_array[$var->name],$var, $output, $user);
            }
            else
                $answer = $this->save_new_standard_answer($var,$output, $user);
            if($answer["saved"] != true)
                $saved = false;
        }
        return ["saved" => $saved];

    }

    private function save_new_standard_answer($var, $output, $user) {
        $answer = new Answer();
        $answer->user_id = $user->id;
        $answer->assignment_id = $this->assignment->id;
        $answer->question_id = $this->id;
        $answer->variable_id = $var->id;
        return $this->update_standard_answer($answer,$var, $output, $user);
    }

    private function update_standard_answer($answer,$var, $output, $user) {
        $values = $output['values'];
        //\Debugbar::alert($var, $values);
        if ($var->type == self::DECIMAL_VARIABLE)
            $answer->submission = $values[$var->name];
        else if ($var->type == self::COLUMN_VARIABLE)
            $answer->submission = implode("|", $values[$var->name]);
        else if (is_array($values[$var->name]))
            $answer->submission = implode("|", $values[$var->name]);
        else
            $answer->submission = $values[$var->name];
        $saved = $answer->save();
        return ["saved" => $saved];
    }

    private function save_simple_answer($value, $user) {
        $answer = Answer::updateOrCreate(['user_id'=>$user->id,'question_id'=>$this->id],
            ['submission' => $value]
        );
        $saved = $answer != null;
        return ["saved" => $saved];
    }

    private function save_results($output, $user, $rescore = false) {
        $result = Result::firstOrNew(['user_id'=>$user->id,'question_id'=>$this->id],
            ['attempts' => 0]
        );
        $result->earned = $output["earned"];
        $result->feedback = $output['output'];

        //If this is a rescore operation, don't update the timestamps and don't increment the attempts
        if($rescore)
            $result->timestamps = false;
        else
            $result->attempts++;

        //If this is a quiz, set the result to deferred.
        if($this->check_deferred())
            $result->status = 3;

        $saved = $result->save();

        return ["saved" => $saved, "attempts" => $result->attempts];
    }

    private function check_saved($output, $answer, $result) {
        if(!$answer["saved"] || !$result["saved"]) {
            $output["valid"] = false;
            $output["output"] = "Error saving. Submission not recorded.";
        }
        return $output;
    }
}
