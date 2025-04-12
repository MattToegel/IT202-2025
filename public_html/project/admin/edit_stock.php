<?php
//note we need to go up 1 more directory
require(__DIR__ . "/../../../partials/nav.php");

if (!has_role("Admin")) {
    flash("You don't have permission to view this page", "warning");
    die(header("Location: $BASE_PATH" . "/home.php"));
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
    $db = getDB();
    $query = "UPDATE `IT202-S25-Stocks` SET ";

    $params = [];
    //per record
    foreach ($quote as $k => $v) {

        if ($params) {
            $query .= ",";
        }
        //be sure $k is trusted as this is a source of sql injection
        $query .= "$k=:$k";
        $params[":$k"] = $v;
    }

    $query .= " WHERE id = :id";
    $params[":id"] = $id;
    error_log("Query: " . $query);
    error_log("Params: " . var_export($params, true));
    try {
        $stmt = $db->prepare($query);
        $stmt->execute($params);
        flash("Updated record ", "success");
    } catch (PDOException $e) {
        error_log("Something broke with the query" . var_export($e, true));
        flash("An error occurred", "danger");
    }
}

$stock = [];
if ($id > -1) {
    //fetch
    $db = getDB();
    $query = "SELECT symbol, open, low, high, price, change_percent, latest_trading_day, volume FROM `IT202-S25-Stocks` WHERE id = :id";
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

?>
<div class="container-fluid">
    <h3>Edit Stock</h3>
    <form method="POST">
        <div class="mb-3">
            <label for="symbol">Stock Symbol</label>
            <input type="text" name="symbol" id="symbol" placeholder="Stock Symbol" required value="<?php se($stock, "symbol"); ?>">
        </div>
        <div class="mb-3">
            <label for="open">Stock Open</label>
            <input type="number" name="open" id="open" placeholder="Stock Open" required value="<?php se($stock, "open"); ?>">
        </div>
        <div class="mb-3">
            <label for="low">Stock Low</label>
            <input type="number" name="low" id="low" placeholder="Stock Low" required value="<?php se($stock, "low"); ?>">
        </div>
        <div class="mb-3">
            <label for="high">Stock High</label>
            <input type="number" name="high" id="high" placeholder="Stock High" required value="<?php se($stock, "high"); ?>">
        </div>
        <div class="mb-3">
            <label for="price">Stock Current Price</label>
            <input type="number" name="price" id="price" placeholder="Stock Current Price" required value="<?php se($stock, "price"); ?>">
        </div>
        <div class="mb-3">
            <label for="change_percent">Stock % change</label>
            <input type="number" step="0.01" name="change_percent" id="change_percent" placeholder="Stock % change" required value="<?php se($stock, "change_percent"); ?>">
        </div>
        <div class="mb-3">
            <label for="volume">Stock Volume</label>
            <input type="number" name="volume" id="volume" placeholder="Stock Volume" required value="<?php se($stock, "volume"); ?>">
        </div>
        <div class="mb-3">
            <label for="latest_trading_day">Stock Date</label>
            <input type="date" name="latest_trading_day" id="latest_trading_day" placeholder="Stock Date" required value="<?php se($stock, "latest_trading_day"); ?>">
        </div>
        <input type="submit" value="Update" class="btn btn-primary">
    </form>

</div>


<?php
//note we need to go up 1 more directory
require_once(__DIR__ . "/../../../partials/flash.php");
?>