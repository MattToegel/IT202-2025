<?php
//ignore this, it's just for output formatting
function newline(){
  //attempt to create newline for command line or browser, can ignore
  echo "<br>\n";
}
$arr1 = [];//creates empty array
var_dump($arr1);
newline();
$arr2 = array();//also creates an empty array
var_dump($arr2);
newline();
//creates an indexed array (where indexes start at 0 and go to N)
$arr3 = array("This","is","an","indexed","array");
var_dump($arr3);
newline();
//You access an index like so
echo $arr3[0]; // output's "This"
newline();
//creates an associative array.
$arr4 = array("key1"=>"value1", "key2"=>"value2", "key3"=>"value3");
//arrays are key/value pair lists
//indexed just use a numberical key
//whereas associative use an alphanumerical key
var_dump($arr4);
newline();
echo $arr4["key1"];//access the value by passing the key into the array
newline();
//we can also set values by index or key
$arr4[4] = "added value";
var_dump($arr4);
newline();
$arr4["key4"] = "value4";
var_dump($arr4);
newline();

//finally lets make a multidimensional array of our samples
$arr5 = array($arr1, $arr2, $arr3, $arr4);
echo "<pre>";
var_export($arr5);
echo "</pre>";
?>