<?php
//note we need to go up 1 more directory
require(__DIR__ . "/../../../partials/nav.php");

if (!has_role("Admin")) {
    flash("You don't have permission to view this page", "warning");
    die(header("Location:" .get_url("landing.php")));
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
        $db = getDB();
        $query = "INSERT INTO `IT202-M25-Companies` ";
        $columns = [];
        $params = [];
        //use first record to build query
        foreach ($companies[0] as $k => $v) {
            array_push($columns, "`$k`");
            $params[":$k"] = $v;
        }
        $query .= "(" . join(",", $columns) . ")";
        $query .= "VALUES (" . join(",", array_keys($params)) . ")";
        // generally it's best to do a bulk insert, but in this case will do one-by-one
        // that way if one record fails, at least some records should succeed
        foreach ($companies as $comp) {
            foreach ($comp as $k => $v) {
                // bind individual data; any pre-insert mapping can be done here
                $params[":$k"] = $v;
                if(in_array($k, ["symbol", "currency"])) {
                    $params[":$k"] = strtoupper($v); // ensure symbol is uppercase
                }
            }
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
    } else {
        flash("No company fetched or provided", "warning");
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
                <label for="keyword">Company keyword</label>
                <input type="search" name="keyword" id="keyword" placeholder="Company keyword" required>
            </div>
            <input type="hidden" name="action" value="fetch">
            <input type="submit" value="Fetch" class="btn btn-primary">
        </form>
    </div>
    <div id="create" style="display: none;" class="tab-target">
        <form method="POST">
            <div class="mb-3">
                <label for="symbol">Company Symbol</label>
                <input type="text" name="symbol" id="symbol" placeholder="Company Symbol" required>
            </div>
            <div class="mb-3">
                <label for="name">Company Name</label>
                <input type="text" name="name" id="name" placeholder="Company Name" required>
            </div>
            <div class="mb-3">
                <label for="type">Company Type</label>
                <input type="text" name="type" id="type" placeholder="Company Type" required>
            </div>
            <div class="mb-3">
                <label for="region">Company Region</label>
                <input type="text" name="region" id="region" placeholder="Company Region" required>
            </div>
            <div class="mb-3">
                <label for="currency">Company Currency</label>
                <input type="text" maxlength="4" name="currency" id="currency" placeholder="Company Currency" required>
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