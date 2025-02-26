<?php
$message = "Hello World";
//right now this will just assign a variable and do nothing else, so we need to do sometihng with it in the next lines.
//we can echo out the variable
echo $message;
//we can include it in an output string
echo "My variable contains: $message";
//however pay close attention to the symbols/characters that wrap the text.
//if we want to evaluate the variable inside the text we must use double quotes.
//see what happens if we instead use single quotes
echo 'My variable contains: $message';
//notice that the output is a literal string.
//we can also use the concatenation operator . to join text (strings) and variables
echo "My variable contains: " . $message;
//We need to make sure to appropriately use the concat operator when applicable. Most languages use the + operator, but in PHP you'll get
//odd results in some cases
//echo $message + $message;



//echo 1 + $message;


$num = 5;
//echo $num + $message;
echo $num . $message;
