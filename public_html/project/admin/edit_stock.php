<?php
//note we need to go up 1 more directory
require(__DIR__ . "/../../../partials/nav.php");

if (!has_role("Admin")) {
    flash("You don't have permission to view this page", "warning");
    die(header("Location: " . get_url("landing.php")));
}
?>

<?php
$id = se($_GET, "id", -1, false);
//TODO handle stock fetch
if (isset($_POST["symbol"])) {
    foreach ($_POST as $k => $v) {
        if (!in_array($k, ["symbol", "open", "low", "high", "price", "change_percent", "volume", "latest_trading_day"])) {
            unset($_POST[$k]);
        }
        $quote = $_POST;
        error_log("Cleaned up POST: " . var_export($quote, true));
    }
    // Ideally only the table name should need to change for most queries
    //update data
    $quote["id"] = $id; // add id to the stock array for the update
    try {
        $quote = uppercaseSymbolCurrency([$quote])[0];
        $r = update("IT202-M25-Stocks", $quote);
        if ($r["rowCount"]) {
            flash("Updated " . $r["rowCount"] . " record(s)", "success");
        } else {
            flash("Error updating record (this can occur if no properties changed)", "warning");
        }
    } catch (PDOException $e) {
        error_log("Something broke with the query" . var_export($e, true));
        flash("An error occurred", "danger");
    } catch (Exception $e) {
        error_log("Something broke with the query" . var_export($e, true));
        flash("An error occurred: " . $e->getMessage(), "danger");
    }
}

$stock = [];
if ($id > -1) {
    //fetch
    $db = getDB();
    $query = "SELECT symbol, open, low, high, price, change_percent, latest_trading_day, volume FROM `IT202-M25-Stocks` WHERE id = :id";
    try {
        $stmt = $db->prepare($query);
        $stmt->execute([":id" => $id]);
        $r = $stmt->fetch();
        if ($r) {
            $stock = $r;
        }
    } catch (PDOException $e) {
        error_log("Error fetching record: " . var_export($e, true));
        flash("Error fetching record", "danger");
    }
} else {
    flash("Invalid id passed", "danger");
    die(header("Location:" . get_url("admin/list_stocks.php")));
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
        "rules" => ["required" => true, "step" => "0.01"]
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
if ($stock) {
    // map the result data to the form (sticky forms)
    $keys = array_keys($stock);

    foreach ($form as $k => $v) {
        if (in_array($v["name"], $keys)) {
            $form[$k]["value"] = $stock[$v["name"]];
        }
    }
}
?>
<div class="container-fluid">
    <h3>Edit Stock</h3>
    <form method="POST">
        <?php foreach ($form as $field): ?>
            <?php render_input($field); ?>
        <?php endforeach; ?>
        <?php render_button(["text" => "Update", "type" => "submit"]); ?>
    </form>

</div>


<?php
//note we need to go up 1 more directory
require_once(__DIR__ . "/../../../partials/flash.php");
?>