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
    $db = getDB();
    $query = "INSERT INTO `IT202-M25-Stocks` ";
    $columns = [];
    $params = [];
    //per record
    foreach ($quote as $k => $v) {
        array_push($columns, "`$k`");
        $params[":$k"] = $v;
    }
    $query .= "(" . join(",", $columns) . ")";
    $query .= "VALUES (" . join(",", array_keys($params)) . ")";
    error_log("Query: " . $query);
    error_log("Params: " . var_export($params, true));
    try {
        $stmt = $db->prepare($query);
        $stmt->execute($params);
        flash("Inserted record " . $db->lastInsertId(), "success");
    } catch (PDOException $e) {
        error_log("Something broke with the query" . var_export($e, true));
        flash("An error occurred", "danger");
    }
}

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
            <div>
                <label for="symbol">Stock Symbol</label>
                <input type="search" name="symbol" id="symbol" placeholder="Stock Symbol" required>
            </div>
            <input type="hidden" name="action" value="fetch">
            <input type="submit" value="Fetch" class="btn btn-primary">
        </form>
    </div>
    <div id="create" style="display: none;" class="tab-target">
        <form method="POST">
            <div class="mb-3">
                <label for="symbol">Stock Symbol</label>
                <input type="text" name="symbol" id="symbol" placeholder="Stock Symbol" required>
            </div>
            <div class="mb-3">
                <label for="open">Stock Open</label>
                <input type="number" name="open" id="open" placeholder="Stock Open" required>
            </div>
            <div class="mb-3">
                <label for="low">Stock Low</label>
                <input type="number" name="low" id="low" placeholder="Stock Low" required>
            </div>
            <div class="mb-3">
                <label for="high">Stock High</label>
                <input type="number" name="high" id="high" placeholder="Stock High" required>
            </div>
            <div class="mb-3">
                <label for="price">Stock Current Price</label>
                <input type="number" name="price" id="price" placeholder="Stock Current Price" required>
            </div>
            <div class="mb-3">
                <label for="change_percent">Stock % change</label>
                <input type="number" name="change_percent" id="change_percent" placeholder="Stock % change" required>
            </div>
            <div class="mb-3">
                <label for="volume">Stock Volume</label>
                <input type="number" name="volume" id="volume" placeholder="Stock Volume" required>
            </div>
            <div class="mb-3">
                <label for="latest_trading_day">Stock Date</label>
                <input type="date" name="latest_trading_day" id="latest_trading_day" placeholder="Stock Date" required>
            </div>
            <input type="hidden" name="action" value="create">
            <input type="submit" value="Create" class="btn btn-primary">
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