<?php
//note we need to go up 1 more directory
require(__DIR__ . "/../../../partials/nav.php");

if (!has_role("Admin")) {
    flash("You don't have permission to view this page", "warning");
    die(header("Location: $BASE_PATH" . "/home.php"));
}
?>

<?php

//TODO handle stock fetch
if (isset($_POST["action"])) {
    $action = $_POST["action"];
    $symbol =  strtoupper(se($_POST, "symbol", "", false));
    $series = [];

    if ($action === "fetch") {
        if ($symbol) {
            $result = fetch_crypto_dailies($symbol);

            error_log("Data from API" . var_export($result, true));
            if ($result) {
                $series = $result; // helper function already sets "is_api"
            }
        } else {
            flash("You must provide a keyword", "warning");
        }
    } else if ($action === "create") {
        foreach ($_POST as $k => $v) {
            // remove keys that aren't part of your data
            // this is both for security and for our dynamic DB logic to work correctly
            // the keys must match the column names of your table
            if (!in_array($k, ["currency_symbol", "currency_name", "average", "date"])) {
                unset($_POST[$k]);
            }
        }
        $_POST["is_api"] = 0;
        $series = [$_POST]; // convert to array format so both fetch/create follow same shape
        
        error_log("Cleaned up POST: " . var_export($series, true));
    }
    
    //insert data - Below should only really need the table name changes
    // the query building should work for all regular inserts
    if (count($series) > 0) {
        try {
            $r = insert("IT202-S25-Crypto", $series, ["debug" => true, "update_duplicate" => true]);
            flash("Data: " . var_export($r, true));
            if ($r["lastInsertId"] || $r["rowCount"] > 0) {
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
    } else {
        flash("No crypto symbol fetched or provided", "warning");
    }
}

// represent form as data
$form = [
    [
        "type" => "text",
        "id" => "symbol",
        "name" => "currency_symbol",
        "label" => "Currency Symbol",
        "rules" => ["required" => true, "maxlength" => 6]
    ],
    [
        "type" => "text",
        "id" => "name",
        "name" => "currency_name",
        "label" => "Currency Name",
        "rules" => ["required" => true, "maxlength" => 20]
    ],
    [
        "type" => "number",
        "id" => "average",
        "name" => "average",
        "label" => "Average",
        "rules" => ["required" => true, "min"=> 0]
    ],
    [
        "type" => "date",
        "id" => "date",
        "name" => "date",
        "label" => "Date",
        "rules" => ["required" => true]
    ],
];

?>
<div class="container-fluid">
    <h3>Create or Fetch Company</h3>
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
            <div class="mb-3">
                <?php render_input(["type" => "text", "name" => "symbol", "id" => "Symbol", "label" => "Symbol", "rules" => ["required" => true]]); ?>
            </div>
            <input type="hidden" name="action" value="fetch">
            <?php render_button(["text" => "Fetch", "type" => "submit"]); ?>
        </form>
    </div>
    <div id="create" style="display: none;" class="tab-target">

        <form method="POST">
            <?php foreach ($form as $field): ?>
                <div class="mb-3">
                    <?php render_input($field); ?>
                </div>
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
require_once(__DIR__ . "/../../../partials/footer.php");
?>