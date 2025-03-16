<?php
namespace App\Library;

use Exception;

class Token
{
    const OPERATOR = 0;
    const IDENTIFIER = 1;
    const NUMBER = 2;
    const STRING = 3;

    public $type;
    public $value;
    public function __construct($type, $token)
    {
        $this->value = $token;
        if ($type == "operator") {
            $this->type = self::OPERATOR;
        } elseif ($type == "identifier") {
            $this->type = self::IDENTIFIER;
        } elseif ($type == "number") {
            $this->type = self::NUMBER;
        } else {
            $this->type = self::STRING;
        }
    }
}

class Lexer
{
    private $expr;
    private $split;
    private $tokens;
    private $variables;
    private $iter_id;
    public function __construct($expr, $variables)
    {
        $this->variables = $variables;
        //$this->expr = str_replace(" ", "", $expr);  //Old string replacement to get rid of whitespace in formulas.  Now using regex below.
        $regex = '~"[^"]*"(*SKIP)(*F)|\s+~';
        $this->expr = preg_replace($regex,"",$expr);  //This regex should eliminate whitespace except when inside double quotes.
        $this->tokenize();
        $this->iter_id = 0;
    }

    public function peek()
    {
        if ($this->iter_id == count($this->tokens)) {
            return null;
        }

        return $this->tokens[$this->iter_id];
    }

    public function next()
    {
        if ($this->iter_id == count($this->tokens)) {
            return null;
        }
        $toRet = $this->tokens[$this->iter_id]; // Element to return
        $this->iter_id += 1; // Increment
        return $toRet;
    }

    // Split the expression by the names and make an array of
    // tokens and save it in $this->tokens
    private function tokenize()
    {
        $delimeters = $this->getAllDelimeters();
        $split = $this->splitWithDelimeter($this->expr, $delimeters);
        // print_r($split);

        $this->tokens = array();
        $allowed_string = null;
        foreach ($split as $t) {
            // if token is wrapped in quotes, treat is an a string, allow it to
            // be parsed by creating STRING token.  The quotes will get stripped
            // the Evaulator to avoid STRING tokens getting replaced with a value
            // if the string in the STRING token matches a variable name
            if (strpos($t, '"') === 0 && strrpos($t, '"', 1) === strlen($t) - 1) {
                //$allowed_string = trim($t, '"');
                //$t = trim($t, '"');
                $allowed_string = $t;
            }

            array_push($this->tokens, $this->getToken($t, $allowed_string));
        }
    }

    // Parse a sigal token and create an appropriate Token object
    private function getToken($str, $allowed_string)
    {
        $operators = $this->getAllowedOperators();
        $functions = $this->getAllIdentifiers();

        if (in_array($str, $operators)) {
            return new Token("operator", $str);
        } elseif (in_array($str, $functions)) {
            return new Token("identifier", $str);
        } elseif (is_numeric($str)) {
            return new Token("number", $str);
        } elseif (strcmp($str, $allowed_string) == 0) {
            return new Token("string", $str);
        }
        throw new Exception("Unknown identifier '$str' in expression");
    }

    // Splits the given string with the given delimeters in an array
    // Each delimiter if put into a seperate array element, and the pieces in between are put in order
    // String $string, string to parse; String[] $delimeters, delimeters to split by
    private function splitWithDelimeter($string, $delims)
    {
        usort($delims, function ($a, $b) {
            return strlen($b) - strlen($a);
        });

        $len = strlen($string);

        $last = 0;
        $lastWasDelim = false;
        $a = array();

        //\Debugbar::addMessage('Checking string '.$string,'test');
        for ($i = 0; $i < $len; $i++) {
            //\Debugbar::addMessage("Checking ".$i." ".strpos($string, '"',$i));

            //First check that if the character at $i is a " initiating the beginning of a string.  If it is, pull the string off as a token.
            if(strpos($string, '"',$i) === $i) {
                $last = strpos($string,'"',$i+1);
                $toAdd = substr($string,$i,$last-$i+1);
                array_push($a, $toAdd);
                //\Debugbar::addMessage('Found string '.$toAdd.' at '.$i.' moving to '.$last,'test');
                $i = $last;
                $last++;
            }

            //Pull delimiters off as tokens
            elseif ($this->isDelimeter($string, $i, $delims)) {
                $d = $this->isDelimeter($string, $i, $delims);
                $from = $last;
                $l = $i - $last;
                $toAdd = substr($string, $last, $l);
                if ($toAdd != "") {
                    array_push($a, $toAdd);
                }
                array_push($a, $d);
                $last = $i + strlen($d);
                $i += strlen($d) - 1;
            }
        }
        $toAdd = substr($string, $last, $len - $last);
        if ($toAdd != "") {
            array_push($a, $toAdd);
        }
        return $a;
    }

    // Checks a $string at starting point $i if the any of the delimeters match from $delims
    // String $string; Int $i; String[] $delims
    private function isDelimeter($string, $i, $delims)
    {
        foreach ($delims as $d) {
            if ($i + strlen($d) <= strlen($string)) {
                $substr = substr($string, $i, strlen($d));
                if ($d == $substr) {
                    if (!$this->isNegativeSciNotation($string, $i)) {
                        return $d;
                    }
                }
            }
        }
        return false;
    }

    // Returns true if $string is valid negative scientific notation
    private function isNegativeSciNotation($string, $i)
    {
        //print("<h4>$string</h4>");
        //print("substr = ".substr($string, $i, 1)."<br>");
        if (substr($string, $i, 1) != "-") {
            return false;
        }
        //print("Found -<br>");
        if ($i < 2 || !is_numeric(substr($string, $i-2, 1))) {
            return false;
        }
        //print("Found #<br>");
        if ($i < 1 || substr($string, $i-1, 1) != "e") {
            return false;
        }
        //print("Found e<br>");
        if ($i >= strlen($string) - 1 || !is_numeric(substr($string, $i+1, 1))) {
            return false;
        }
        //print("Found #<br>");
        return true;
    }

    // Get all allowered operators and functions
    public function getAllDelimeters()
    {
        return array_merge($this->getAllowedOperators(),
                           $this->getAllowedFunctions(),
                           $this->getLibraryFunctions(),
                           $this->variables);
    }

    // Get all library and built-in functions and variable names
    public function getAllIdentifiers()
    {
        return array_merge($this->getAllowedFunctions(),
                           $this->getLibraryFunctions(),
                           $this->variables);
    }

    public function getAllowedOperators()
    {
        // List of allowed operators
        $allowedOperators  = array(
            " ",
            ",",
            "&&",
            "||",
            "+",
            "-",
            "*",
            "/",
            "^",
            "(",
            ")",
            "!",
            "<=",
            ">=",
            "!=",
            "==", // Split by == first so we can easily replace with "="
            "=",
            "<",
            ">",
            "true",
            "false"
        );
        return $allowedOperators;
    }

    public function getFirstLevelOperators() // Addition level
    {
        // List of allowed operators
        return array(
            "&&",
            "||",
            "+",
            "-",
            "!=",
            "==", // Split by == first so we can easily replace with "="
            "=",
        );
    }

    public function getRelationalOperators() // Relational operators
    {
        // List of allowed operators
        return array(
            ">",
            "<",
            "<=",
            ">=",
        );
    }

    public function getUnaryOperators()
    {
        // List of allowed operators
        $allowedOperators  = array(
            "!",
            "-"
        );
        return $allowedOperators;
    }

    public function getAllowedFunctions()
    {
        // List of allowed functions
        $allowedFunctions = array(
            "abs",
            "acos",
            "acosh",
            "asin",
            "asinh",
            "atan2",
            "atan",
            "atanh",
            "base_convert",
            "bindec",
            "ceil",
            "cos",
            "cosh",
            "decbin",
            "dechex",
            "decoct",
            "deg2rad",
            "exp",
            "expm1",
            "floor",
            "fmod",
            "getrandmax",
            "hexdec",
            "hypot",
            "is_finite",
            "is_infinite",
            "is_nan",
            "lcg_value",
            "log10",
            "log1p",
            "log",
            "max",
            "min",
            "mt_getrandmax",
            "mt_rand",
            "mt_srand",
            "octdec",
            "pi",
            "pow",
            "rad2deg",
            "random_int",
            "round",
            "sin",
            "sinh",
            "sqrt",
            "srand",
            "tan",
            "tanh"
        );

        return $allowedFunctions;
    }

    public function getLibraryFunctions()
    {
        $libraryFunctions = array(
            "solved",
            "withinRange",
            "withinPercent",
            "slope",
            "stringCheck",
            "stringStartsWith",
            "intercept",
            "residual",
            "col_div",
            "col_mult",
            "scalar_div",
            "scalar_mult",
            "col_func",
            "map",
            "mean",
            "stdev",
            "concatenate",
            "count",
            "sigfigs",
            "precision",
            "array",
            "element",
            "indexOf",
            "factorial",
            "fuzzyMap",
            "lpMin",
            "rand",
            "random_float",
            "random_element",
            "sigs",
            "addNoise",
            "each",
            "eachString",
            "slope_error",
            "intercept_error",
            "count_true",
            "mantissa",
            "array_min",
            "array_max",
            "checkChemical",
            "checkChemicalSymbols",
            "checkChemicalCharge",
            "checkChemicalPhase",
            "email",
            "decimal",
            "sigsDecimal",
            "substr",
            "strpos",
        );
        return $libraryFunctions;
    }
}
