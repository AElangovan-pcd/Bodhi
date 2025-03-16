<?php
namespace App\Library;
use Exception;

class RegressionFunctions
{
      public static function performOperations($numbers, $variable, $operation)
      {
            $m = new EvalMath;
            $m->evaluate("f(".$variable.") = ".$operation);
            $newX = array();
            foreach ($numbers as $key => $x) {
                  if (!is_numeric($x) || strpos($x, 'e') || strpos($x, 'E')) {
                        $pattern = "/(x10\^|\s10\^)/";
                        $x = preg_replace($pattern, "*10^", $x);
                        $x = str_replace("e", "*10^", $x);
                        $x = str_replace("E", "*10^", $x);
                  }
                  $newX[] = $m->evaluate("f(".$x.")");
            }
            return $newX;
      }

      public static function linear_regression($x, $y)
      {
            //from https://richardathome.wordpress.com/2006/01/25/a-php-linear-regression-function/
            // calculate number points
            $n = count($x);

            // ensure both arrays of points are the same size
            if ($n != count($y)) {
                  throw new Exception("Unequal number of elements in regression");
            }
            // calculate sums
            $x_sum = array_sum($x);
            $y_sum = array_sum($y);
            $xx_sum = 0;
            $xy_sum = 0;
            $yy_sum = 0;

            for($i = 0; $i < $n; $i++) {
                  $xy_sum+=($x[$i]*$y[$i]);
                  $xx_sum+=($x[$i]*$x[$i]);
                  $yy_sum+=($y[$i]*$y[$i]);
            }

            // calculate slope
            $m = (($n * $xy_sum) - ($x_sum * $y_sum)) / (($n * $xx_sum) - ($x_sum * $x_sum));

            // calculate intercept
            $b = ($y_sum - ($m * $x_sum)) / $n;

            // calculate r
            $r = ($xy_sum - ((1/$n)*$x_sum*$y_sum))/
                  (sqrt((($xx_sum)-((1/$n)*(pow($x_sum,2))))*(($yy_sum)-((1/$n)*(pow($y_sum,2))))));

            $r2 = $r*$r;
            // return result
            return array("m"=>$m, "b"=>$b, "r"=> $r,"r2"=> $r2);
      }

      public static function prepForLinReg($x, $y, $vars)
      {
            $xname = $x;
            $yname = $y;

            // get array of user inputs from variable names
            foreach ($vars as $key => $var) {
                  if (isset($var['name'])){
                        if ($var['name'] == $xname){
                              $xCol = $var->getColumn();
                        }
                        if ($var['name'] == $yname){
                              $yCol = $var->getColumn();
                        }
                  }
                  else{
                        if($key === $xname)
                              $xCol = $var->getColumn();
                        if($key === $yname)
                              $yCol = $var->getColumn();
                  }
            }
            //format operations (eg. 1/x) for performOperations()
            $xOps = str_replace("\"", '',$x);
            $xOps = str_replace("'", '',$xOps);
            $yOps = str_replace("\"", '',$y);
            $yOps = str_replace("'", '',$yOps);

            //perform the operations
            $xFinal = RegressionFunctions::performOperations($xCol, strtolower($xname), strtolower($xOps));
            $yFinal = RegressionFunctions::performOperations($yCol, strtolower($yname), strtolower($yOps));

            //plug into linear_regression()
            return RegressionFunctions::linear_regression($xFinal, $yFinal);
      }
}
