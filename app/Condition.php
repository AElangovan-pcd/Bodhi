<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Library\Evaluator;
use Auth;
use App\Score;

class Condition extends Model
{
    public function question() {
        return $this->belongsTo('App\Question');
    }

    //TODO Deprecate
    public function scores() {
        return $this->hasMany('App\Score');
    }

    /*------------------------------------------------------------------------
     * Evaluation functions
     *------------------------------------------------------------------------
     */

    // Evaluates the condition, and if it evaluates to true, return the $result
    public function evaluate($variables)
    {
        $data = $variables; // Set the array var with $var[name] = value, so the eval'd code can reference it
        try
        {
            $eval = new Evaluator($data);
            // print("Evaluating equation $this->equation\n");
            $r = $eval->getValue($this->equation);
        }
        catch (Exception $e)
        {
            $error  =  "Error evaluating condition: ".$e->getMessage();
            $cond = $this->equation;
            $error .= " in condition '$cond'";
            return array('output' => $error, 'valid' => false, "interVars" => array(), 'score' => null);
        }

        if  (($this->type && $r) || (!$this->type && !$r)) // Return if true
        {

            return array(
                'output'		=> $this->result,
                'valid'			=> true,
                'interVars'		=> $this->getInterVariables($variables),
                'values'		=> $variables,
                'earned'		=> $this->points,
            );
        }

        return false;
    }

    private function getInterVariables($data)
    {
        $inters = $this->question->interVariables;
        $ret = array();
        foreach ($inters as $inter)
        {
            $ret[$inter->name] = $data[$inter->name];
        }
        return $ret;
    }
}
