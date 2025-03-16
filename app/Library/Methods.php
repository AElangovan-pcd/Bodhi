<?php
namespace App\Library;
use Exception;
use MathPHP\Statistics\Regression;

class EvaluateMethods
{
	const MSG_TO_USER = 1;

	public static function dispatch($operator, $args)
	{
		$stdFuncs = self::getStandardFunctions();
		$spcFuncs = self::getSpecialFunctions();
		if ( array_key_exists($operator, $stdFuncs) )
		{
			// Only apply a single argument
			return self::apply($stdFuncs[$operator], $operator, $args);
		}
		else if ( array_key_exists($operator, $spcFuncs) )
		{
			return call_user_func_array($spcFuncs[$operator], $args);
		}
	}

	public static function apply($opFunc, $operatorName, $args)
	{
		$arrayFuncs = self::getArrayParameterFunctions();

		if (count($args) != $opFunc["numArgs"] && !in_array($operatorName, $arrayFuncs))
			throw new Exception("Wrong number of arguments passed to '$operatorName' function");

		if ($opFunc["numArgs"] == 0) {
		    return call_user_func($opFunc['f']);
        }

		else if ($opFunc["numArgs"] == 1) // Vectorize over 1st argument
		{
			$arg = $args[0];

			// for funcs that take an array as single value
			if (in_array($operatorName, $arrayFuncs)) {
				return call_user_func($opFunc['f'], $args);
			}
			elseif (is_array($arg))
			{
				$a = array();
				for($i = 0; $i < count($arg); $i++)
				{
					$args_arr = array_merge([ $arg[$i] ], array_slice($args, 1));
					$a[$i] = call_user_func_array($opFunc['f'], $args_arr);
				}
				return $a;
			}

			return call_user_func_array($opFunc['f'], $args);
		}
		else if ($opFunc["numArgs"] >= 2) // Vectorize over 1st and 2nd argument
		{
			$a = $args[0];
			$b = $args[1];

			if (is_array($a) && is_array($b))
			{
				if (count($a) != count($b))
					throw new Exception("Array size mistmatch for arguments of '$operatorName' function.");

				for ($i = 0; $i < count($a); $i++)
				{
					$args_arr = array_merge([ $a[$i], $b[$i] ], array_slice($args, 2));
					$a[$i] = call_user_func_array($opFunc['f'], $args_arr);
				}
				return $a;
			}
			else if (is_array($a))
			{
				for ($i = 0; $i < count($a); $i++)
				{
					$args_arr = array_merge([ $a[$i], $b ], array_slice($args, 2));
					$a[$i] = call_user_func_array($opFunc['f'], $args_arr);
				}
				return $a;
			}
			else if (is_array($b))
			{
				for ($i = 0; $i < count($b); $i++)
				{
					$args_arr = array_merge([ $a, $b[$i] ], array_slice($args, 2));
					$b[$i] = call_user_func_array($opFunc['f'], $args_arr);
				}
				return $b;
			}

			return call_user_func_array($opFunc['f'], $args);
		}
	}

	public static function getStandardFunctions()
	{
		return array(
			"abs" 			=> array( "numArgs" => 1, "f" => function($a){ return abs($a); }),
			"acos" 			=> array( "numArgs" => 1, "f" => function($a){ return acos($a); }),
			"acosh" 		=> array( "numArgs" => 1, "f" => function($a){ return acosh($a); }),
			"asin" 			=> array( "numArgs" => 1, "f" => function($a){ return asin($a); }),
			"asinh" 		=> array( "numArgs" => 1, "f" => function($a){ return asinh($a); }),
			"atan2" 		=> array( "numArgs" => 2, "f" => function($a, $b) { return atan2($a, $b); }),
			"atan" 			=> array( "numArgs" => 1, "f" => function($a){ return atan($a); }),
			"atanh" 		=> array( "numArgs" => 1, "f" => function($a){ return atanh($a); }),
			"bindec" 		=> array( "numArgs" => 1, "f" => function($a){ return bindec($a); }),
			"ceil" 			=> array( "numArgs" => 1, "f" => function($a){ return ceil($a); }),
			"cos" 			=> array( "numArgs" => 1, "f" => function($a){ return cos($a); }),
			"cosh" 			=> array( "numArgs" => 1, "f" => function($a){ return cosh($a); }),
			"decbin" 		=> array( "numArgs" => 1, "f" => function($a){ return decbin($a); }),
			"dechex" 		=> array( "numArgs" => 1, "f" => function($a){ return dechex($a); }),
			"decoct"		=> array( "numArgs" => 1, "f" => function($a){ return decoct($a); }),
			"deg2rad" 		=> array( "numArgs" => 1, "f" => function($a){ return deg2rad($a); }),
			"exp" 			=> array( "numArgs" => 1, "f" => function($a){ return exp($a); }),
			"expm1" 		=> array( "numArgs" => 1, "f" => function($a){ return expm1($a); }),
			"floor" 		=> array( "numArgs" => 1, "f" => function($a){ return floor($a); }),
			"fmod" 			=> array( "numArgs" => 1, "f" => function($a){ return fmod($a); }),
			"hexdec" 		=> array( "numArgs" => 1, "f" => function($a){ return hexdec($a); }),
			"hypot" 		=> array( "numArgs" => 1, "f" => function($a){ return hypot($a, $b); }),
			"is_finite" 	=> array( "numArgs" => 1, "f" => function($a){ return is_finite($a); }),
			"is_infinite" 	=> array( "numArgs" => 1, "f" => function($a){ return is_infinite($a); }),
			"is_nan" 		=> array( "numArgs" => 1, "f" => function($a){ return is_nan($a); }),
			"lcg_value" 	=> array( "numArgs" => 0, "f" => function(){ return lcg_value(); }),
			"log10" 		=> array( "numArgs" => 1, "f" => function($a){ return log10($a); }),
			"log1p" 		=> array( "numArgs" => 1, "f" => function($a){ return log1p($a); }),
			"log" 			=> array( "numArgs" => 1, "f" => function($a){ return self::LPlog($a); }),
			"max" 			=> array( "numArgs" => 1, "f" => function($a){ return max($a); }),
			"min" 			=> array( "numArgs" => 1, "f" => function($a){ return min($a); }),
			"octdec" 		=> array( "numArgs" => 1, "f" => function($a){ return octdec($a); }),
			"pi" 			=> array( "numArgs" => 0, "f" => function(){ return pi(); }),
			"pow" 			=> array( "numArgs" => 2, "f" => function($a, $b){ return pow($a, $b); }),
			"rad2deg" 		=> array( "numArgs" => 1, "f" => function($a){ return rad2deg($a); }),
            //"round" 		=> array( "numArgs" => 1, "f" => function($a){ return round($a); }),
			//"rand" 	    	=> array( "numArgs" => 0, "f" => function(){ return rand(); }),
            "random_int"    => array( "numArgs" => 2, "f" => function($a, $b) { return random_int($a,$b); }),
			"sin" 			=> array( "numArgs" => 1, "f" => function($a){ return sin($a); }),
			"sinh" 			=> array( "numArgs" => 1, "f" => function($a){ return sinh($a); }),
			"sqrt" 			=> array( "numArgs" => 1, "f" => function($a){ return sqrt($a); }),
			"srand" 		=> array( "numArgs" => 1, "f" => function($a){ return srand($a); }),
			"tan" 			=> array( "numArgs" => 1, "f" => function($a){ return tan($a); }),
			"tanh" 			=> array( "numArgs" => 1, "f" => function($a){ return tanh($a); }),
			"withinRange" 	=> array( "numArgs" => 3, "f" => function($a, $b, $c){ return self::withinRange($a, $b, $c); }),
			"withinPercent" => array( "numArgs" => 3, "f" => function($a, $b, $c){ return self::withinPercent($a, $b, $c); }),

		);
	}

	public static function getSpecialFunctions()
	{
		return array(
			"slope"       => function($a, $b) { return self::slope($a, $b); },
			"intercept"   => function($a, $b) { return self::intercept($a, $b); },
			"residual"    => function($a, $b) { return self::residual($a, $b); },
			"map"         => function($var, $case, $one, $map, $two, $map2, ...$rest) { return self::map($var, $case, $one, $map, $two, $map2, ...$rest); },
			"fuzzyMap"    => function($var, $range, $one, $map, $two, $map2, ...$rest) { return self::fuzzyMap($var, $range, $one, $map, $two, $map2, ...$rest); },
			"stringCheck" => function($var,$case,$s1,...$rest) {return self::stringCheck($var,$case,$s1, ...$rest);},
            "stringStartsWith" => function($var,$case,$num,$str) {return self::stringStartsWith($var,$case,$num,$str);},
			"mean"		  => function($var) {\Debugbar::addMessage('mean'); \Debugbar::debug($var);return self::mean($var); },
			"stdev"	 	  => function($var) {return self::stdev($var); },
            "concatenate" => function($var,...$var2) {return self::concatenate($var,...$var2);},
			"count"	  	  => function($var) {return self::LPcount($var); },
			"sigfigs"	  => function($var) {return self::sigfigs($var); },
			"precision"	  => function($var) {return self::precision($var); },
            "decimal"	  => function($var) {return self::decimal($var); },
			"array"	  	  => function($var,...$var2) {return self::LParray($var,...$var2);},
			"element"	  => function($arr,$index) {return self::element($arr,$index); },
            "indexOf"	  => function($arr,$value) {return self::indexOfLP($arr,$value); },
			"lpMin"		  => function($var1,$var2) {return min($var1,$var2);},
            "round"       => function($var,...$precision) {return self::lpRound($var,...$precision);},
            "rand"        => function() {return lcg_value();},
            "random_float" => function($min,$max) {return ($min+lcg_value()*(abs($max-$min))); },
            "random_element" => function($var,...$var2) {return self::random_element($var,...$var2);},
            "sigs"        => function($value, $digits) {return self::sigs($value, $digits);},
            "sigsDecimal" => function($value, $digits) {return self::sigsDecimal($value, $digits);},
            "factorial"   => function($var) {return self::factorial($var);},
            "addNoise"    => function($var, $scale, $iterations = 1) {return self::addNoise($var, $scale, $iterations);},
            "each"        => function($arr1, $arr2, $func, $tol = 0) {return self::each($arr1, $arr2, $func, $tol);},
            "eachString"        => function($arr1, $arr2, $func, $case = 0) {return self::eachString($arr1, $arr2, $func, $case);},
            "slope_error" => function($x, $y) { return self::slope_error($x,$y);},
            "intercept_error" => function($x, $y) { return self::intercept_error($x,$y);},
            "count_true" => function($var) {return self::count_true($var);},
            "mantissa"    => function($var) {return self::mantissa($var);},
            "array_min" => function($var) {return min($var);},
            "array_max" => function($var) {return max($var);},
            "checkChemical" => function($var, $formula, $charge = 0, $phase = '') {return self::checkChemical($var, $formula, $charge, $phase);},
            "checkChemicalSymbols" => function($var, $formula) {return self::checkChemicalSymbols($var, $formula);},
            "checkChemicalCharge" => function($var, $charge = 0) {return self::checkChemicalCharge($var, $charge);},
            "checkChemicalPhase" => function($var, $phase) {return self::checkChemicalPhase($var, $phase);},
            "email" => function() {return self::userEmail();},
            "substr" => function($string, $offset, $length = null) {return substr($string, $offset, $length);},
            "strpos" => function($haystack, $needle, $offset = 0) {return strpos($haystack, $needle, $offset);},
		);
	}

	public static function getArrayParameterFunctions()
	{
		return array(
			"min",
			"max"
		);
	}

	// Returns true if the $value given is within plus or minus $range from the $center
	public static function withinRange($value, $center, $range, $inclusive = true)
	{
		if ($inclusive) // Inclusive (>=)
			return ($value <= $center + $range && $value >= $center - $range);
		else // Exlusive (>)
			return ($value < $center + $range && $value > $center - $range);
	}

	// Returns true if $value is within $percent (0 to 100) of $value realtive to $center
	public static function withinPercent($value, $center, $percent, $inclusive = true)
	{
		return EvaluateMethods::withinRange($value, $center, abs($percent / 100 * $center), $inclusive);
	}

	public static function slope($x, $y)
	{
		if (!is_array($x) || !is_array($y))
			throw new Exception("Arrays must be used as arguments to regression functions");

	    $lin = RegressionFunctions::linear_regression($x, $y);
	    return $lin['m']; //return slope
	}

	public static function intercept($x, $y)
	{
		if (!is_array($x) || !is_array($y))
			throw new Exception("Arrays must be used as arguments to regression functions");

	    $lin = RegressionFunctions::linear_regression($x, $y);
	    return $lin['b']; //return inercept
	}

	public static function residual($x, $y)
	{
		if (!is_array($x) || !is_array($y))
			throw new Exception("Arrays must be used as arguments to regression functions");

	    $lin = RegressionFunctions::linear_regression($x, $y);
	    return $lin['r2']; //return residual
	}

	public static function mean($var)
	{
		if (!is_array($var))
			throw new Exception("The mean function only accepts array variables");

		$avg = array_sum($var)/count($var);

		return $avg;

	}

    // Function to calculate standard deviation (uses sd_square).  Based on http://stackoverflow.com/questions/5434648/z-scoresstandard-deviation-and-mean-in-php
    public static function stdev($var)
    {
        if (!is_array($var))
            throw new Exception("The std_dev function only accepts array variables");

        // If there is only one element, return zero (though perhaps it should be undefined).
        if(count($var) == 1)
            return 0;

        // square root of sum of squares divided by N-1
        $std_dev = sqrt(array_sum(array_map("self::sd_square", $var, array_fill(0,count($var), (array_sum($var) / count($var)) ) ) ) / (count($var)-1) );;

        return $std_dev;
    }

	// Function to calculate square of value - mean.  Helper function for std_dev function.
	public static function sd_square($x, $mean) { return pow($x - $mean,2); }

	public static function LParray($a,...$rest)
	{
		$new_arr=array($a);
		foreach($rest as $val) {
			array_push($new_arr,$val);
		}
		return $new_arr;
	}

    public static function indexOfLP($arr,$value)
    {
        if (!is_array($arr))
            throw new Exception("The indexOf function requires that the first argument is an array.");

        $index=array_search($value,$arr);

        /*if($index === false)
            throw new Exception($value . " is not a valid option.");*/

        return $index;
    }

    public static function concatenate($a,...$rest)
    {
        foreach($rest as $val) {
            $a.=$val;
        }
        return $a;
    }

	public static function element($arr,$index)
	{
		if (!is_array($arr))
			throw new Exception("The element function requires that the first argument is an array.");
		if ($index>count($arr)-1)
			throw new Exception("The requested index in the element function is larger than the size of the array.");

		$value=$arr[$index];

		return $value;
	}

    public static function indexOf($arr,$value)
    {
        if (!is_array($arr))
            throw new Exception("The indexOf function requires that the first argument is an array.");

        $index=array_search($value,$arr);

        if($index === false)
            throw new Exception($value . " is not a valid option.");

        return $index;
    }

	public static function LPcount($var)
	{
		if (!is_array($var))
			throw new Exception("The LPcount function only accepts array variables");

		$count = count($var);

		return $count;
	}

	public static function random_element($a,...$rest) {
        $new_arr=array($a);
        foreach($rest as $val) {
            array_push($new_arr,$val);
        }
        $index = random_int(0,count($new_arr)-1);
        return $new_arr[$index];
    }

	public static function sigfigs($str)
	{
		$pos = stripos($str,'e');
		if($pos!==false) {
			$str = stristr($str,"e",true);  //Get only the numbers before the exponent.
		}
		if(stripos($str,'.') === false)
		    $str = rtrim($str,'0'); //Trim trailing zeroes if there is no decimal point.

		$str = ltrim($str, '-'); // Trim negative sign
		$str = ltrim($str,'0'); //Trim leading zeroes.
        $str = ltrim($str, '.'); //Trim decimal if it's the first thing
        $str = ltrim($str, '0'); //Trim leading zeroes (doing this again in case it was all leading zeroes before the decimal place).
        $str = str_replace('.','',$str); //Remove the decimal so it doesn't get counted as a significant figure.
        $sigfigs = strlen($str);

		return $sigfigs;
	}

	//Gets the order of magnitude of the precision of the value entered.  Currently only works for values smaller than one (returns 0 if the value is larger than 1).
	public static function precision($var)
	{
		$exp=0;
		if (($pos = stripos($var, "e")) !== FALSE) { //Take care of scientific notation.
			$exp = substr($var, $pos+1); //Get only the exponent.
			$var = stristr($var,"e",true);  //Get only the numbers before the exponent.
		}

		$post = strlen(substr(strrchr($var, "."), 1));
		if ($exp<0) $post=$post+abs($exp);
		if ($post>0) $precision="1e-".$post;
		else $precision = 0;

		return $precision;
	}



	public static function map($var, $case, $one, $map, $two, $map2, ...$rest) {
		$mapped = null;
		$error = "Please enter a valid option: $one, $two";

        $length = count($rest);
        if ($length & 1) {					// This lets you optionally set a default condition if nothing else matches.
            $mapped = $rest[$length-1];
            $length=$length-1;
        }

		if($case==0) {
			if (strtolower($var) == strtolower($one))
				$mapped=$map;
			elseif(strtolower($var) == strtolower($two))
				$mapped=$map2;
			else {
				$i = 0;
				while ($i < $length) {
					if (strtolower($var) == strtolower($rest[$i]))
						$mapped = $rest[$i + 1];

					$error .= ", $rest[$i]";
					$i += 2;
				}
			}
		}
		else {
			if ($var == $one)
				$mapped = $map;
			else if ($var == $two)
				$mapped = $map2;
			else {
				$i = 0;
				while ($i < $length) {
					if ($var == $rest[$i])
						$mapped = $rest[$i + 1];

					$error .= " $rest[$i],";
					$i += 2;
				}
			}
		}

		if ($mapped == null)
			throw new Exception($error, self::MSG_TO_USER);

		return $mapped;
	}

	public static function fuzzyMap($var, $range, $one, $map, $two, $map2, ...$rest) {
		$mapped = null;
		$error = "Please enter a valid option: $one, $two,";

		$length = count($rest);
		if ($length & 1) {					// This lets you optionally set a default condition if nothing else matches.
			$mapped = $rest[$length-1];
			$length=$length-1;
		}

		if ( EvaluateMethods::withinRange($var, $one, $range) )
			$mapped = $map;
		else if (EvaluateMethods::withinRange($var, $two, $range))
			$mapped = $map2;
		else {
			$i = 0;

			while ($i < $length) {
				if (EvaluateMethods::withinRange($var, $rest[$i], $range))
					$mapped = $rest[$i + 1];
				$error .= " $rest[$i],";
				$i += 2;
			}
		}

		if ($mapped == null)
			throw new Exception($error, self::MSG_TO_USER);

		return $mapped;
	}

	public static function stringCheck($var,$case,$s1, ...$rest) {
		$stringFound=0;

		if($case==0) {
			if(strcmp(strtolower($var),strtolower($s1))==0)
				$stringFound=1;
		}
		else {
			if(strcmp($var,$s1)==0)
				$stringFound=1;

		}


		foreach($rest as $s2) {
			if($case==0) {
				if(strcmp(strtolower($var),strtolower($s2))==0)
					$stringFound=1;
			}
			else {
				if(strcmp($var,$s2)==0)
					$stringFound=1;
			}
		}
		return $stringFound;
	}

	private static function stringStartsWith($var,$case,$num,$str) {
	    $var = substr($var,0,$num);
	    $str = substr($str,0,$num);
	    $var = $case == 0 ? strtolower($var) : $var;
	    $str = $case == 0 ? strtolower($str) : $str;

	    if(strcmp($var,$str)==0)
	        return true;
	    return false;
    }

	private static function LPlog($a) {
		if ($a == 0)
			throw new Exception("Log(0) is undefined");
		return log($a);
	}

    private static function lpRound($var,...$rest) {
        if(!is_array($var))
	        return self::applyRound($var,...$rest);

        foreach($var as $key => $element) {
            $var[$key] = self::applyRound($element,...$rest);
        }
        return $var;
    }

    private static function applyRound($var,...$rest) {
        if(count($rest)==0)
            return round($var);
        if($rest[0] < 0)
            return round($var, $rest[0]);
        else
            return number_format($var, $rest[0], '.', '');
            //return round($var,$rest[0]);
    }

    // Gets decimal precision of the value. Returns negative values for numbers with trailing zeroes that are not significant. 4.20 return 2, 420 returns -1.
    private static function decimal($var) {
        $sf = self::sigfigs($var);

        $order = floor(log10(abs($var)));
        $mantissa = $var == 0 ? 0 : (abs($var) / pow(10, $order));
        $num = (string) self::sigs($mantissa, $sf);
        $sub = $sf === 1 ? 1 : 2;

        $precision = strlen($num) - $sub - $order;
        return $precision;

    }

    private static function sigsDecimal($value, $digits) {
        if ($value == 0) {
            $decimalPlaces = $digits - 1;
        } elseif ($value < 0) {
            $decimalPlaces = $digits - floor(log10($value * -1)) - 1;
        } else {
            $decimalPlaces = $digits - floor(log10($value)) - 1;
        }
        return $decimalPlaces;
    }

    public static function sigs($value, $digits) {
        if(!is_array($value))
            return self::applySigs($value, $digits);

        foreach($value as $key => $element) {
            $value[$key] = self::applySigs($element,$digits);
        }
        return $value;
    }

    private static function applySigs($value, $digits) {
        if ($value == 0) {
            $decimalPlaces = $digits - 1;
        } elseif ($value < 0) {
            $decimalPlaces = $digits - floor(log10($value * -1)) - 1;
        } else {
            $decimalPlaces = $digits - floor(log10($value)) - 1;
        }

        $answer = ($decimalPlaces > 0) ?
            number_format($value, $decimalPlaces, ".", "") : round($value, $decimalPlaces);
        return $answer;
    }

    private static function factorial($number) {
	    if($number==0)
	        return 1;
	    if($number < 0)
            throw new Exception("The factorial function does not accept negative numbers.");
        # Check if your value is an integer
        if ( strval($number) !== strval(intval($number)) ) {
            throw new Exception("The factorial function only accepts integer values.");
        }
        $factorial = 1;
        for ($i = 1; $i <= $number; $i++){
            $factorial = $factorial * $i;
        }
        return $factorial;
    }

    private static function addNoise($var, $scale, $iterations = 1) {

        if (!is_array($var))
            return self::applyNoise($var, $scale, $iterations);

        foreach($var as $key => $element) {

            $var[$key] = self::applyNoise($element, $scale, $iterations);
        }
        return $var;
    }

    private static function applyNoise($var, $scale, $iterations) {
        $noise = 0;
        for($i=0; $i<$iterations; $i++) {
            $noise += lcg_value();
        }
        $var += $scale*($noise-$iterations/2);
        return $var;
    }

    private static function each($arr1, $arr2, $func, $tol) {
	    if(!is_array($arr1) || !is_array($arr2))
            throw new Exception("The two variables used in 'each' must be arrays.");
	    if(count($arr1) != count($arr2))
	        throw new Exception("Unequal number of elements in arrays");

	    if($func == 'exact') {
	        for($i = 0; $i< count($arr1); $i++) {
	            if($arr1[$i] != $arr2[$i])
	                return false;
            }
	        return true;
        }
	    elseif($func == 'withinPercent') {
            for($i = 0; $i< count($arr1); $i++) {
                if(!self::withinPercent($arr2[$i],$arr1[$i],$tol))
                    return false;
            }
            return true;
        }
        elseif($func == 'withinRange') {
            for($i = 0; $i< count($arr1); $i++) {
                if(!self::withinRange($arr2[$i],$arr1[$i],$tol))
                    return false;
            }
            return true;
        }
	    throw new Exception("Invalid function supplied for function each.");

    }

    private static function eachString($arr1, $arr2, $func, $case) {

	    //Trim leading and trailing whitespace from each element array.
	    $arr1 = array_map('trim',$arr1);
	    $arr2 = array_map('trim',$arr2);

        if($case == 0) {  //If case-insensitive
            $arr1 = array_map('mb_strtolower',$arr1);
            $arr2 = array_map('mb_strtolower',$arr2);
        }

        if($func == 'exactPermute') {  //Allow the arrays to be in any order
            sort($arr1);
            sort($arr2);
            $func = "exact" ;
        }
        return self::each($arr1, $arr2, $func, 0);
    }

    private static function slope_error($x, $y) {
        $regression = self::regression($x,$y);
        $se = $regression->standardErrors();  // [m => se(m), b => se(b)]
        return $se["m"];
    }

    private static function intercept_error($x,$y) {
        $regression = self::regression($x,$y);
        $se = $regression->standardErrors();  // [m => se(m), b => se(b)]
        return $se["b"];
    }

    private static function regression($x,$y) {
        if (!is_array($x) || !is_array($y))
            throw new Exception("Arrays must be used as arguments to regression functions");
        if(count($x) != count($y)) {
            throw new Exception("An equal number of data points must be provided for regressions");
        }
        $points = [];
        foreach($x as $key => $xval) {
            array_push($points, [$xval, $y[$key]]);
        }
        $regression = new Regression\Linear($points);
        return $regression;
    }

    private static function count_true($var) {
	    $count = 0;
	    foreach($var as $v) {
	        if($v == true)
	            $count++;
        }
        return $count;
    }

    private static function mantissa($val) {
	    if($val == 0)
	        return $val;
	    try {
            $order = floor(log10(abs($val)));
            $mantissa = $val / pow(10, $order);
            return $mantissa;
        }
        catch(\Exception $e) {
	        return 0;
        }
    }

    public static function checkChemicalFormula($sub, $ans) {
	    $sub = json_encode($sub);
	    if($ans->formulaType == "Exact")
	        $formula = self::checkChemicalSymbols($sub, $ans->formula ?? '');

	    if($ans->formulaType == "Molecular")
	        $formula = self::checkMolecularFormula($sub->formula, $ans->formula);


        return [
            "formula" => $formula,
            "charge" => self::checkChemicalCharge($sub, $ans->charge ?? ''),
            "phase" => self::checkChemicalPhase($sub, $ans->phase ?? ''),
        ];
    }

    private static function checkMolecularFormula($a, $b) {
        throw new Exception("Molecular Formula Parsing Not Currently Available");
    }

    private static function checkChemical($var, $formula, $charge, $phase) {
	    $var = json_decode($var);
	    $f = $var->formula ?? '';
	    $c = $var->charge ?? '';
	    $p = $var->phase ?? '()';
        $charge = self::adaptChemicalCharges($charge);
        $phase = self::adaptChemicalPhases($phase);
	    return ($formula == $f && $charge == $c && $phase == $p);
    }

    private static function checkChemicalSymbols($var, $formula) {
        $var = json_decode($var);
        $f = $var->formula ?? '';
        return trim($formula) == trim($f);
    }

    private static function checkChemicalCharge($var,$charge) {
        $var = json_decode($var);
        $c = $var->charge ?? '';
        $charge = self::adaptChemicalCharges($charge);
        return $charge == $c;
    }

    private static function checkChemicalPhase($var, $phase) {
        $var = json_decode($var);
        $p = $var->phase ?? '()';
        $phase = self::adaptChemicalPhases($phase);
        return $phase == $p;
    }

    private static function adaptChemicalCharges($charge) {

	    if(strpos($charge, "+") !== false && strpos($charge, "+") > 0 || $charge === "+")
	        return $charge;  //$charge = str_replace("+", "", $charge);
	    if(strpos($charge, "-") !== false && strpos($charge, "-") > 0 || $charge === "-")
	        return $charge;

        if($charge === 0 || $charge == "0" || $charge == null)
            return '';
        elseif($charge === 1 || $charge === '1')
            return "+";
        elseif($charge === -1 || $charge === '-1')
            return "-";
        elseif($charge > 0 ) {
            return $charge . "+";
        }
        elseif($charge < 0 ) {
            return abs($charge) . "-";
        }
        return $charge;
    }

    private static function adaptChemicalPhases($phase) {
	    if(strpos($phase, "(") === false)
	        return ("(".trim($phase).")");
	    return $phase;
    }

    private static function userEmail() {
	    return \Auth::user()->email;
    }

}
