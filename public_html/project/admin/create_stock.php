<?php
//note we need to go up 1 more directory
require(__DIR__ . "/../../../partials/nav.php");

if (!has_role("Admin")) {
    flash("You don't have permission to view this page", "warning");
    die(header("Location: " . get_url("landing.php")));
}
?>

<?php

//TODO handle stock fetch
if (isset($_POST["action"])) {
    $action = $_POST["action"];
    $symbol =  strtoupper(se($_POST, "symbol", "", false));
    $quote = [];
    if ($symbol) {
        if ($action === "fetch") {
            $result = fetch_quote($symbol);

            error_log("Data from API" . var_export($result, true));
            if ($result) {
                $quote = $result;
                $quote["is_api"] = 1;
            }
        } else if ($action === "create") {
            foreach ($_POST as $k => $v) {
                // remove keys that aren't part of your data
                // this is both for security and for our dynamic DB logic to work correctly
                // the keys must match the column names of your table
                if (!in_array($k, ["symbol", "open", "low", "high", "price", "change_percent", "volume", "latest_trading_day"])) {
                    unset($_POST[$k]);
                }
            }
            $quote = $_POST;
            $quote["is_api"] = 0;
            error_log("Cleaned up POST: " . var_export($quote, true));
        }
    } else {
        flash("You must provide a symbol", "warning");
    }
    //insert data - Below should only really need the table name changes
    // the query building should work for all regular inserts
    try {
        $quote = uppercaseSymbolCurrency([$quote])[0];
        $r = insert("IT202-M25-Stocks", $quote, ["update_duplicate" => true]);
        if ($r["lastInsertId"]) {
            flash("Inserted record " . $r["lastInsertId"], "success");
        } else {
            flash("Error inserting record", "warning");
        }
    } catch (PDOException $e) {
        error_log("Something broke with the query" . var_export($e, true));
        flash("An error occurred", "danger");
    } catch (Exception $e) {
        error_log("Something broke with the query" . var_export($e, true));
        flash("An error occurred: " . $e->getMessage(), "danger");
    }
}
// represent form as data
$form = [
    [
        "type" => "text",
        "id" => "symbol",
        "name" => "symbol",
        "label" => "Stock Symbol",
        "rules" => ["required" => true]
    ],
    [
        "type" => "number",
        "id" => "open",
        "name" => "open",
        "label" => "Stock Open",
        "rules" => ["required" => true]
    ],
    [
        "type" => "number",
        "id" => "low",
        "name" => "low",
        "label" => "Stock Low",
        "rules" => ["required" => true]
    ],
    [
        "type" => "number",
        "id" => "high",
        "name" => "high",
        "label" => "Stock High",
        "rules" => ["required" => true]
    ],
    [
        "type" => "number",
        "id" => "price",
        "name" => "price",
        "label" => "Stock Current Price",
        "rules" => ["required" => true]
    ],
    [
        "type" => "number",
        "id" => "change_percent",
        "name" => "change_percent",
        "label" => "Stock % change",
        "rules" => ["required" => true]
    ],
    [
        "type" => "number",
        "id" => "volume",
        "name" => "volume",
        "label" => "Stock Volume",
        "rules" => ["required" => true]
    ],
    [
        "type" => "date",
        "id" => "latest_trading_day",
        "name" => "latest_trading_day",
        "label" => "Stock Date",
        "rules" => ["required" => true]
    ]
];

//TODO handle manual create stock
?>
<div class="container-fluid">
    <h3>Create or Fetch Stock</h3>
    <ul class="nav nav-tabs">
        <li class="nav-item">
            <a class="nav-link bg-success" href="#" onclick="switchTab('create')">Fetch</a>
        </li>
        <li class="nav-item">
            <a class="nav-link bg-success" href="#" onclick="switchTab('fetch')">Create</a>
        </li>
    </ul>
    <div id="fetch" class="tab-target">
        <form method="POST">
            <?php render_input(["type" => "text", "name" => "symbol", "id" => "symbol", "label" => "Stock Symbol", "rules" => ["required" => true]]); ?>
            <input type="hidden" name="action" value="fetch">
            <?php render_button(["text" => "Fetch", "type" => "submit"]); ?>
        </form>
    </div>
    <div id="create" style="display: none;" class="tab-target">
        <form method="POST">
            <?php foreach ($form as $field): ?>
                <?php render_input($field); ?>
            <?php endforeach; ?>
            <input type="hidden" name="action" value="create">
            <?php render_button(["text" => "Create", "type" => "submit"]); ?>
        </form>
    </div>
</div>
<script>
    function switchTab(tab) {
        let target = document.getElementById(tab);
        if (target) {
            let eles = document.getElementsByClassName("tab-target");
            for (let ele of eles) {
                ele.style.display = (ele.id === tab) ? "none" : "block";
            }
        }
    }
</script>

<?php
//note we need to go up 1 more directory
require_once(__DIR__ . "/../../../partials/flash.php");
?>