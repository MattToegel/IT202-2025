<?php
require(__DIR__ . "/base.php");
$a1 = [
    ["id" => 1, "name" => "Sparrow", "size" => "small", "color" => "brown", "region" => "North America"],
    ["id" => 2, "name" => "Robin", "size" => "small", "color" => "red", "region" => "Europe"]
];

$a2 = [
    ["id" => 3, "name" => "Eagle", "size" => "large", "color" => "brown", "region" => "Worldwide"],
    ["id" => 4, "name" => "Parrot", "size" => "medium", "color" => "green", "region" => "Tropical"]
];

$a3 = [
    ["id" => 5, "name" => "Penguin", "size" => "medium", "color" => "black and white", "region" => "Antarctica"],
    ["id" => 6, "name" => "Flamingo", "size" => "large", "color" => "pink", "region" => "Africa"]
];

$a4 = [
    ["id" => 7, "name" => "Owl", "size" => "medium", "color" => "white", "region" => "Worldwide"],
    ["id" => 8, "name" => "Hummingbird", "size" => "small", "color" => "varied", "region" => "Americas"]
];

function processBirds($birds) {
    printProblemData($birds);
    echo "<br>Subset output:<br>";
    
    // Note: use the $birds variable to iterate over, don't directly touch $a1-$a4
    // TODO Objective: Extract the name, color, region into a separate multi-dimension array called $subset
    $subset = []; // result array
    // Start edits
    
    // End edits
    echo "<pre>" . var_export($subset, true) . "</pre>";
   
}
$ucid = "mt85"; // replace with your UCID
printHeader($ucid, 1); 
?>
<table>
    <thead>
        <tr>
            <th>A1</th>
            <th>A2</th>
            <th>A3</th>
            <th>A4</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>
                <?php processBirds($a1); ?>
            </td>
            <td>
                <?php processBirds($a2); ?>
            </td>
            <td>
                <?php processBirds($a3); ?>
            </td>
            <td>
                <?php processBirds($a4); ?>
            </td>
        </tr>
    </tbody>
</table>
<?php printFooter($ucid,1); ?>
<style>
    table {
        border-spacing: 1em 3em;
        border-collapse: separate;
    }

    td {
        border-right: solid 1px black;
        border-left: solid 1px black;
    }
</style>