<?php
namespace App\Library;
use Exception;

class Parser
{
	private $expression, $variables;
	private $expr, $lexer;
	public function __construct($expression, $variables)
	{
		$this->expression = $expression;
		$this->variables = $variables;
		$this->lexer = new Lexer($expression, $variables);
		$this->expr = $this->parseExpression();
	}

	public static function matchOp($token, $op)
	{
		if ($token == null)
			return false;

		return ($token->type == Token::OPERATOR &&
			   $token->value === $op);
	}

	public function getExpression()
	{
		return $this->expr;
	}

	public function parseExpression()
	{
		return $this->parseConditional();
	}

	public function parseConditional()
	{
		$expr = $this->parseEquality();
		$token = $this->lexer->peek();

		while( self::matchOp($token, "&&") || self::matchOp($token, "||") )
		{
			$token = $this->lexer->next();
			$expr = array(
					"Binary" => array(
						"operator" 	=> $token->value,
						"left" 		=> $expr,
						"right" 	=> $this->parseEquality()
					)
				);
			$token = $this->lexer->peek();
		}
		return $expr;
	}

	public function parseEquality()
	{
		$expr = $this->parseRelational();
		$token = $this->lexer->peek();

		while( self::matchOp($token, "=") || self::matchOp($token, "==") || self::matchOp($token, "!=") )
		{
			$token = $this->lexer->next();
			$expr = array(
					"Binary" => array(
						"operator" 	=> $token->value,
						"left" 		=> $expr,
						"right" 	=> $this->parseRelational()
					)
				);
			$token = $this->lexer->peek();
		}
		return $expr;
	}

	public function parseRelational()
	{
		$expr = $this->parseAdditive();
		$token = $this->lexer->peek();

		while( $this->matchOneOp($token, $this->lexer->getRelationalOperators() ) )
		{
			$token = $this->lexer->next();
			$expr = array(
					"Binary" => array(
						"operator" 	=> $token->value,
						"left" 		=> $expr,
						"right" 	=> $this->parseAdditive()
					)
				);
			$token = $this->lexer->peek();
		}
		return $expr;
	}

	public function parseAdditive()
	{
		$expr = $this->parseMultiplicative();
		$token = $this->lexer->peek();

		while( self::matchOp($token, "+") || self::matchOp($token, "-") )
		{
			$token = $this->lexer->next();
			$expr = array(
					"Binary" => array(
						"operator" 	=> $token->value,
						"left" 		=> $expr,
						"right" 	=> $this->parseMultiplicative()
					)
				);
			$token = $this->lexer->peek();
		}
		return $expr;
	}

	public function parseMultiplicative()
	{
        $expr = $this->parseExponential();
		$token = $this->lexer->peek();

		while( self::matchOp($token, "*") || self::matchOp($token, "/") )
		{
			$token = $this->lexer->next();
			$expr = array(
					"Binary" => array(
						"operator" 	=> $token->value,
						"left" 		=> $expr,
						"right" 	=> $this->parseExponential()
					)
			);
			$token = $this->lexer->peek();
		}
		return $expr;
	}

	public function parseExponential()
    {
        $expr = $this->parseUnary();
        $token = $this->lexer->peek();

        while( self::matchOp($token, "^") )
        {
            $token = $this->lexer->next();
            $expr = array(
                "Binary" => array(
                    "operator" 	=> $token->value,
                    "left" 		=> $expr,
                    "right" 	=> $this->parseExponential() //$this->parseUnary()
                )
            );

            $token = $this->lexer->peek();
        }

        return $expr;
    }

	public function parseUnary()
	{
		$expr;
		$token;

		$token = $this->lexer->peek();
		if( self::matchOp($token, "+") || self::matchOp($token, "-") || self::matchOp($token, "!"))
		{
			$token = $this->lexer->next();
			$expr = $this->parseUnary();
			return array(
					"Unary" => array(
						"operator" 		=> $token->value,
						"expression" 	=> $expr,
					)
				);
		}
		return $this->parsePrimary();
	}

	public function parsePrimary()
	{
		$expr;
		$token = $this->lexer->peek();

		if ($token->type === Token::IDENTIFIER)
		{
			$token = $this->lexer->next();
			if (self::matchOp($this->lexer->peek(), "("))
			{
				return $this->parseFunctionCall($token->value);
			}
			else
			{
				return array("Identifier" => $token->value);
			}
		}


		if ($token->type === Token::NUMBER)
		{
			$token = $this->lexer->next();
			return array("Number" => $token->value);
		}

		if ($token->type === Token::STRING)
		{
			$token = $this->lexer->next();
			return array("String" => $token->value);
		}

		if (self::matchOp($token, "("))
		{
			$this->lexer->next(); // skip the '('
			$expr = $this->parseExpression();
			$token = $this->lexer->next();
			if (!self::matchOp($token, ")"))
			{
				throw new Exception("Expecting ')' in expression");
			}
			else
			{
				return array("Expression" => $expr);
			}
		}

		throw new Exception("Could not process token: ".$token->value);
	}

	public function parseFunctionCall($name)
	{
		$token = array();
		$args = array();

		$token = $this->lexer->next();
		if (!self::matchOp($token, "("))
		{
			throw new Exception("Expecting ( in function call");
		}

		$token = $this->lexer->peek();
		if (!self::matchOp($token, ")"))
		{
			$args = $this->parseArgumentList();

			if ($name == 'map') {
				if (count($args) < 5)
					throw new Exception("Please enter at least 2 mappings.");
				/*if (count($args)%2!=0)
					throw new Exception("Please enter values and mappings in pairs.");*/

				if (isset($args[0]['Number']) && ! in_array($args[0]['Number'], $this->variables)) {
					throw new Exception("The first argument must be a valid variable");
				}
				if (isset($args[0]['Identifier']) && !in_array($args[0]['Identifier'], $this->variables)) {
					throw new Exception("The first argument must be a valid variable");
				}
				if (!isset($args[1]['Number'])) {
					throw new Exception("The second argument must be either 0 or 1.");
				}
				if (!($args[1]['Number']==0 || $args[1]['Number']==1)) {
					$msg=$args[1]['Number'];
					throw new Exception("The second argument must be either 0 or 1.");
				}
			}

			if ($name == 'stringCheck') {
				if (count($args) < 3)
					throw new Exception("Please enter at least three arguments.");

				if (isset($args[0]['Number']) && ! in_array($args[0]['Number'], $this->variables)) {
					throw new Exception("The first argument of strCheck must be a valid variable");
				}
				if (isset($args[0]['Identifier']) && !in_array($args[0]['Identifier'], $this->variables)) {
					throw new Exception("The first argument of strCheck must be a valid variable");
				}
				if (!isset($args[1]['Number'])) {
					throw new Exception("The second argument must be either 0 or 1.");
				}
				if (!($args[1]['Number']==0 || $args[1]['Number']==1)) {
					$msg=$args[1]['Number'];
					throw new Exception("The second argument must be either 0 or 1.");
				}
			}
		}

		$token = $this->lexer->next();
		if (!self::matchOp($token, ")"))
		{
            throw new Exception("Expecting ) in a function call");
		}

		return array(
			"FunctionCall" => array(
				"name" => $name,
				"args" => $args
			)
		);
	}

	public function parseArgumentList()
	{
        $token  = array();
        $expr   = array();
        $args   = array();

        while (true)
        {
            $expr = $this->parseExpression();

            array_push($args, $expr);
            $token = $this->lexer->peek();
            if (!self::matchOp($token, ',')) {
                break;
            }
            $this->lexer->next();
        }

        return $args;
	}


	// Returns true if the $token matches one of the operators
	// in $options
	private function matchOneOp($token, $options)
	{
		if ($token == null)
			return false;
		foreach($options as $opt)
		{
			if (self::matchOp($token, $opt))
				return true;
		}
		return false;
	}

}
