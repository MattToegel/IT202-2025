<?php
function printArrayInfo($arr, $arrayNumber) {
    echo "<div style='color: blue;display: ruby-text'>Problem {$arrayNumber}: Original Array: ";
    foreach($arr as $a){
        echo "<pre><code>[$a]</code></pre>";
    }
    echo "</div><br>";
}
function printHeader($ucid, $problem) {
    $currentDT = date("Y-m-d H:i:s");
    echo "<h2 style='color: purple;'>Running Problem {$problem} for [{$ucid}] [{$currentDT}]</h2>";
    switch ($problem) {
        case 1:
            echo '<p>Objective: Extract the name, color, region into a separate multi-dimension array called $subset</p>';
            break;
        case 2:
            echo '<p>Objective: Add logic to create a new array ($processedCars) with original properties plus age and isClassic. isClassic is a boolean based on today\'s year and the $classic_age variable.</p>';
            break;
        case 3:
            echo '<p>Objective: Add logic to join both arrays on the userId property into one $joined array</p>';
            break;
        default:
            break;
    }
}
function printProblemData($arr){
    echo "<br>Processing Array:<br><pre>" . var_export($arr, true) . "</pre>";
}
function printProblemMultiData($arr1, $arr2){
echo "<br>Processing Arrays:<br><pre>Users: " . var_export($arr1, true) . "<br>Activities: " . var_export($arr2, true) . "</pre>";
}
function printFooter($ucid, $problem) {
    $currentDT = date("Y-m-d H:i:s");
    echo "<h2 style='color: purple;'>Completed Problem {$problem} for [{$ucid}] [{$currentDT}]</h2>";
}


?>