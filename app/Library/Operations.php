<?php
namespace App\Library;
use Exception;

class BinaryMethods
{
	public static function dispatch($operator, $left, $right)
	{
		$opFuncs = self::getFunctions();
		return self::apply($left, $right, $opFuncs[$operator], $operator);
	}

	public static function apply($a, $b, $opFunc, $operator)
	{
		if (is_array($a) && is_array($b))
		{
			if (count($a) != count($b))
				throw new Exception("Array size mismatch for '$operator' operator.");
			for ($i = 0; $i < count($a); $i++)
			{
				$a[$i] = $opFunc($a[$i], $b[$i]);
			}
			return $a;
		}
		else if (is_array($a))
		{
			for ($i = 0; $i < count($a); $i++)
			{
				$a[$i] = $opFunc($a[$i], $b);
			}
			return $a;
		}
		else if (is_array($b))
		{
			for ($i = 0; $i < count($b); $i++)
			{
				$b[$i] = $opFunc($a, $b[$i]);
			}
			return $b;
		}
		return $opFunc($a, $b);
	}

	public static function getFunctions()
	{
		return array(

			"+" => function($a, $b)
			{
			    return $a + $b;
			},

			"-" => function($a, $b)
			{
			    return $a - $b;
			},

			"*" => function($a, $b)
			{
			    return $a * $b;
			},

			"/" => function($a, $b)
			{
			    return $a / $b;
			},

            "^" => function($a, $b)
            {
                return pow($a, $b);
            },

			"<=" => function($a, $b)
			{
			    return $a <= $b;
			},

			"<" => function($a, $b)
			{
			    return $a < $b;
			},

			">=" => function($a, $b)
			{
			    return $a >= $b;
			},

			">" => function($a, $b)
			{
			    return $a > $b;
			},


			"==" => function($a, $b)
			{
			    return $a == $b;
			},

			"=" => function($a, $b)
			{
			    return $a == $b;
			},

			"!=" => function($a, $b)
			{
			    return $a != $b;
			},

			"&&" => function($a, $b)
			{
			    return $a && $b;
			},

			"||" => function($a, $b)
			{
			    return $a || $b;
			},

		);
	}

}

class UnaryMethods
{
	public static function dispatch($operator, $value)
	{
		switch($operator)
		{
			case "!":
				return self::not($value);
			case "-":
				return self::negative($value);
			case "+":
				return $value;
		}
	}

	public static function not($value)
	{
		if (is_array($value))
		{
			for ($i = 0; $i < count($value); $i++)
			{
				$value[$i] = !$value[$i];
			}
			return $value;
		}
		return !$value;
	}

	public static function negative($value)
	{
		if (is_array($value))
		{
			for ($i = 0; $i < count($value); $i++)
			{
				$value[$i] = -$value[$i];
			}
			return $value;
		}
		return - $value;
	}
}
