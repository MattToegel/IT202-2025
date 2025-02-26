<?php
//ignore this, it's just for output formatting
function newline()
{
    //attempt to create newline for command line or browser, can ignore
    echo "<br>\n";
}
$message = "hello world"; //global scope

//this section defines a function
function test()
{
   // echo "My global variable has $message";
}
//these executes/runs the function
test();
newline();
//output should be missing "hello world"
function test2()
{
    $message = "Hello world from inside test2()"; //this is a local scope variable
    echo $message;
}
test2();
newline();
//output should be "Hello world from inside test2();
//but we don't have access to the local variable
echo $message;
newline();
//will result in just "hello world"
//however if we do
function test3()
{
    global $message;
    $message = "Hello world overriden from local";
    echo $message;
}
newline();
test3();
newline();
echo $message;
newline();
//both should output the same text

//finally lets count with static
function increment()
{
    static $count = 0;
    echo "Next: $count";
    newline();
    $count++;
}
increment();
increment();
increment();
increment();
