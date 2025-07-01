<?php
//note we need to go up 1 more directory
require(__DIR__ . "/../../../partials/nav.php");

if (!has_role("Admin")) {
    flash("You don't have permission to view this page", "warning");
    die(header("Location:" . get_url("landing.php")));
}
?>

<?php

//TODO handle stock fetch
if (isset($_POST["action"])) {
    $action = $_POST["action"];
    $keyword =  strtoupper(se($_POST, "keyword", "", false));
    $companies = [];

    if ($action === "fetch") {
        if ($keyword) {
            $result = search_companies($keyword);

            error_log("Data from API" . var_export($result, true));
            if ($result) {
                $companies = $result; // helper function already sets "is_api"
            }
        } else {
            flash("You must provide a keyword", "warning");
        }
    } else if ($action === "create") {
        foreach ($_POST as $k => $v) {
            // remove keys that aren't part of your data
            // this is both for security and for our dynamic DB logic to work correctly
            // the keys must match the column names of your table
            if (!in_array($k, ["symbol", "name", "type", "region", "currency"])) {
                unset($_POST[$k]);
            }
        }
        $_POST["is_api"] = 0;
        $companies = [$_POST]; // convert to array format so both fetch/create follow same shape
        error_log("Cleaned up POST: " . var_export($companies, true));
    }

    //insert data - Below should only really need the table name changes
    // the query building should work for all regular inserts
    if (count($companies) > 0) {
        $companies = uppercaseSymbolCurrency($companies);

        error_log("Transformed companies " . var_export($companies, true));
        try {
            $r = insert("IT202-M25-Companies", $companies, ["debug" => true, "update_duplicate" => true]);
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
        flash("No company fetched or provided", "warning");
    }
}

// represent form as data
$form = [
    [
        "type" => "text",
        "id" => "symbol",
        "name" => "symbol",
        "label" => "Company Symbol",
        "rules" => ["required" => true]
    ],
    [
        "type" => "text",
        "id" => "name",
        "name" => "name",
        "label" => "Company Name",
        "rules" => ["required" => true]
    ],
    [
        "type" => "text",
        "id" => "type",
        "name" => "type",
        "label" => "Company Type",
        "rules" => ["required" => true]
    ],
    [
        "type" => "text",
        "id" => "region",
        "name" => "region",
        "label" => "Company Region",
        "rules" => ["required" => true]
    ],
    [
        "type" => "text",
        "id" => "currency",
        "name" => "currency",
        "label" => "Company Currency",
        "rules" => ["required" => true, "maxlength" => 4]
    ]
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
            <?php render_input(["type" => "text", "name" => "keyword", "id" => "keyword", "label" => "Company keyword", "rules" => ["required" => true]]); ?>
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