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
            echo "<p>Objective: Print out only odd values in a single line separated by commas</p>";
            break;
        case 2:
            echo "<p>Objective: Print out the total sum of the passed array</p>";
            break;
        case 3:
            echo "<p>Objective: Make each array value positive, convert it back to the original data type, and assign it to the proper slot in the output array</p>";
            break;
        case 4:
            echo "<p>Objective:</p>";
            echo "<ul>
                    <li>Challenge 1: Remove non-alphanumeric characters except spaces</li>
                    <li>Challenge 2: Convert text to Title Case</li>
                    <li>Challenge 3: Trim leading/trailing spaces and remove duplicate spaces</li>
                    <li>Result 1-3: Assign final phrase to placeholderForModifiedPhrase</li>
                    <li>Challenge 4: Extract middle 3 characters (beginning starts at middle of phrase), assign to 'placeholderForMiddleCharacters'</li>
                    <li>If not enough characters, assign 'Not enough characters'</li>
                  </ul>";
            break;
        default:
            break;
    }
}
function printFooter($ucid, $problem) {
    $currentDT = date("Y-m-d H:i:s");
    echo "<h2 style='color: purple;'>Completed Problem {$problem} for [{$ucid}] [{$currentDT}]</h2>";
}
function printArrayInfoDouble($arr, $arrayNumber) {
    echo "<p style='color: blue;'>Problem {$arrayNumber}: Original Array: " . implode(", ", array_map(fn($num) => number_format($num, 8), $arr)) . "</p>";
}
function printArrayInfoMixed($arr, $arrayNumber) {
    $formattedArray = array_map(function($item) {
        $type = strtoupper(substr(gettype($item), 0, 1)); // Extract first character of type
        return "$item [$type]";
    }, $arr);

    echo "<p style='color: blue;'>Problem {$arrayNumber}: Original Array: " . implode(", ", $formattedArray) . "</p>";
}

function printOutputWithType($arr) {
    $output = array_map(function($item) {
        if ($item === null) return "<span style='color: red;'>Invalid value</span>";
        $type = strtoupper(substr(gettype($item), 0, 1)); // Extract first character of type
        return "$item [$type]";
    }, $arr);

    echo implode(", ", $output);
}
function printStringTransformations($index, $modifiedPhrase, $middleCharacters) {
    echo("<p>Index[{$index}] \"<pre>{$modifiedPhrase}</pre>\" | Middle: \"<pre>{$middleCharacters}</pre>\"</p>");
}
function printArrayInfoBasic($arr, $arrayNumber) {
    echo "<div style='color: blue;display: ruby-text'>Problem {$arrayNumber}: Original Array: ";
     //. implode(", ", array_map(fn($item) => "<code>[\"$item\"]</code>", $arr)) . "</p>";
    foreach($arr as $a){
        echo "<pre><code>[$a]</code></pre>";
    }
    echo "</div><br>";
    
}

?>
