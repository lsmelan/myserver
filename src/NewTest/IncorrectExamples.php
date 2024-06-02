<?php

namespace App\NewTest;

// Incorrect: Unused import
use SomeLibrary\SomeClass;

class IncorrectExamples
{
    private $property;

    public function __construct( $property )
    {
        $this->property = $property;
    }

    // Incorrect: Method names should be in camelCase
    public function Get_property()
    {
        return $this->property;
    }

    // Incorrect: Multiple lines for a simple operation and no spaces around operators
    public function calculate_product($a, $b) {
        return
            $a*$b;
    }

    // Incorrect: Long array syntax, extra spaces inside array
    public function getItemsIncorrect()
    {
        return array( 'item1' , 'item2' , 'item3' );
    }

    // Incorrect: No function type hinting, no return type, and no spacing around operators
    public function format_string( $input ){
        return trim( $input);
    }

    // Incorrect: No spaces in ternary operator
    public function is_positive($number)
    {
        return $number>0?true:false;
    }

    // Incorrect: No spaces before and after .
    public function display_string()
    {
        return "Hello"." "."World";
    }

    // Incorrect: Line exceeds 120 characters
    public function exceedsLine()
    {
        return "123456123456123456123456123456123456123456123456123456123456123456123456123456123456123456123456123456123456";
    }

    // Incorrect: Improperly formatted multi-line array
    public function get_associative_array()
    {
        return [
            'key1'=>'value1'
            ,'key2'=>'value2'
            ,'key3'=>'value3'
        ];
    }

    // Incorrect: Violating PSR-1 and PSR-2 rules
    public function LONG_FUNCTION_NAME_WITH_UNDERSCORES( $Var1 , $Var2 , $Var3 )
    {
        return $Var1 + $Var2 + $Var3;
    }

    // Incorrect: No visibility declaration
    function noVisibilityDeclaration()
    {
        return true;
    }

    // Incorrect: Only one argument is allowed per line in a multi-line function call
    function twoArgumentsInAMultiLineFunction()
    {
        return $this->retArray(
            array(),
            array(1),array(2)
        );
    }

    // Incorrect: New line before closing parenthesis
    function newLineBeforeClosingParenthesis()
    {
        return $this->retArray(
            [
                1
            ]
        );
    }

    function retArray($array1, $array2=[], $array3=[])
    {
        return $array1+$array2+$array3;
    }

    // Incorrect: Tab indentation instead of spaces
	public function tabIndentation()
	{
	    return "This line is indented with tabs.";
	}
}
?>
