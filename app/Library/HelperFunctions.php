<?php
namespace App\Library;
use Exception;

class HelperFunctions {

    public static function replaceForHTML($string) {
        $replaceMap = Array(
            "'" => "&#39;"
        );

        foreach ($replaceMap as $search => $replace) {
            $string = str_replace($search, $replace, $string);
        }

        return $string;
    }
}
