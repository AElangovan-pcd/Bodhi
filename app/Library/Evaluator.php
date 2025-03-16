<?php
namespace App\Library;
use App\Library\Operations;
use Exception;

class Evaluator
{
	private $expression, $data;
	private $expr, $lexer;

	public function __construct($data)
	{
		$this->data = $data;
		$this->variables = array_keys($data);
	}

	public function getValue($expression)
	{
		$parser = new Parser($expression, $this->variables);
		$expr = $parser->getExpression();
		return $this->evaluate($expr);
	}


	public function evaluate($expr)
	{
		if (isset($expr["Expression"]))
		{
			return $this->evaluate( $expr["Expression"] );
		}
		else if (isset($expr["Identifier"]))
		{
			return $this->data[ $expr["Identifier"] ]; // variable name
		}
		else if (isset($expr["Number"]))
		{
			return $expr["Number"];
		}
		else if (isset($expr["Unary"]))
		{
			return $this->evaluateUnary($expr["Unary"]);
		}
		else if (isset($expr["Binary"]))
		{
			return $this->evaluateBinary($expr["Binary"]);
		}
		else if (isset($expr["FunctionCall"]))
		{
			return $this->evaluateFunction($expr["FunctionCall"]);
		}
		else if (isset($expr["String"]))
		{
			//return $expr["String"];
            return trim($expr["String"], '"');  //Strip the quotes off of the string token.
		}
	}

	public function evaluateUnary($expr)
	{
		$op = $expr["operator"];
		$value = $this->evaluate( $expr["expression"] );
		return UnaryMethods::dispatch($op, $value);
	}

	public function evaluateBinary($expr)
	{
		$op = $expr["operator"];
		$L = $this->evaluate($expr["left"]);
		$R = $this->evaluate($expr["right"]);
		
		return BinaryMethods::dispatch($op, $L, $R);
	}

	public function evaluateFunction($expr)
	{
		$name = $expr["name"];
		$args = array();
		foreach($expr["args"] as $arg)
		{
			array_push($args, $this->evaluate($arg));
		}
		return EvaluateMethods::dispatch($name, $args);
	}
}
