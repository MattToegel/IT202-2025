<?php
/* Don't edit this file directly. Instead, edit the problem files in the same directory. */
function printArrayInfo($arr, $arrayNumber) {
 
    echo "<div style='color: blue;display: block'>Problem {$arrayNumber}: 
    <br>Original Array: 
    <div style='display: inline-flex;align-items: baseline;'>";
    for($i = 0; $i < count($arr); $i++) {
        $a = $arr[$i];
        $comma = $i > 0 ? ", " : "";
       
        echo "$comma&nbsp;<pre><code>$a</code></pre>";
    }
    echo "</div></div><br>";
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
    echo "<p style='color: blue;'>Problem {$arrayNumber}: <br> Original Array: " . implode(", ", array_map(fn($num) => number_format($num, 8), $arr)) . "</p>";
}
function printArrayInfoMixed($arr, $arrayNumber) {
    
    $formattedArray = array_map(function($item) {
        $type = strtoupper(substr(gettype($item), 0, 1)); // Extract first character of type
        $item = str_replace("-","<span style='color: red; font-size:1.5em'>-</span>", $item); // Highlight negative sign
        return "$item [$type]";
    }, $arr);

    echo "<p style='color: blue;'>Problem {$arrayNumber}: 
    <br> Note: The letter in the brackets indicates the data type of the value (I for integer, D for double/float, S for string).
    <br> Original Array: " . implode(", ", $formattedArray) . "</p>";
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
    if (empty($modifiedPhrase)) {
        $modifiedPhrase = "<span style='color:red'>Missing</span>";
    }
    if (empty($middleCharacters)) {
        $middleCharacters = "<span style='color:red'>Missing</span>";
    }
    // print Index [{$index}] = $modifiedPhrase, Middle: $middleCharacters, ensure extra spaces are not truncated if present


    echo("<div style='display: inline-flex; align-items: baseline;'>Index[{$index}] \"<pre>{$modifiedPhrase}</pre>\" | Middle: \"<pre>{$middleCharacters}</pre>\"</div>");
}
function printArrayInfoBasic($arr, $arrayNumber) {
    echo "<div style='color: blue;display: display: block'>Problem {$arrayNumber}: 
    <br>Note: The backticks are just to show all the characters in array slots.
    <br>Original Array: 
    <div style='display: inline-flex;align-items: baseline;'>";
     //. implode(", ", array_map(fn($item) => "<code>[\"$item\"]</code>", $arr)) . "</p>";
    foreach($arr as $a){
        $comma = isset($comma) ? ", " : "";
        echo "$comma&nbsp;<pre><code>`$a`</code></pre>";
    }
    echo "</div></div><br>";
    
}

?>
<?php
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const navLinks = document.querySelectorAll("nav ul li a");
        navLinks.forEach(link => {
            if (link.getAttribute("href") === "<?php echo $currentPage; ?>") {
                link.style.backgroundColor = "#c0c0c0";
                link.style.fontWeight = "bold";
            }
        });
    });
</script>
<style>
    nav ul {
        display: flex;
        gap: 10px;
        padding: 0;
        margin: 0;
    }
    nav ul li {
        list-style: none;
    }
    nav ul li a {
        text-decoration: none;
        padding: 5px 10px;
        background-color: #e0e0e0;
        border-radius: 3px;
        color: black;
    }
    nav ul li a:hover {
        background-color: #d0d0d0;
    }
</style>
<nav >
    <ul >
        <li><a href="problem1.php">Problem 1</a></li>
        <li><a href="problem2.php">Problem 2</a></li>
        <li><a href="problem3.php">Problem 3</a></li>
        <li><a href="problem4.php">Problem 4</a></li>
    </ul>
</nav>