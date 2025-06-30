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
        if (!in_array($k, ["symbol", "name", "type","region","currency"])) {
            unset($_POST[$k]);
        }
        $company = $_POST;
        error_log("Cleaned up POST: " . var_export($company, true));
    }
    // Ideally only the table name should need to change for most queries
    //update data
    $db = getDB();
    $query = "UPDATE `IT202-M25-Companies` SET ";

    $params = [];
    //per record
    foreach ($company as $k => $v) {

        if ($params) {
            $query .= ",";
        }
        //be sure $k is trusted as this is a source of sql 
        // can do any data transformation here
        // Note: backticks are wrapped around the key incase your column
        //names use reserved sql keywords
        $query .= "`$k`=:$k";
        $params[":$k"] = $v;
        if(in_array($k, ["symbol", "currency"])) {
            $params[":$k"] = strtoupper($v); // ensure symbol is uppercase
        }
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

$company = [];
if ($id > -1) {
    //fetch
    $db = getDB();
    $query = "SELECT symbol, name, type, region, currency FROM `IT202-M25-Companies` WHERE id = :id";
    try {
        $stmt = $db->prepare($query);
        $stmt->execute([":id" => $id]);
        $r = $stmt->fetch();
        if ($r) {
            $company = $r;
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
    <h3>Edit Company</h3>
    <form method="POST">
            <div class="mb-3">
                <label for="symbol">Company Symbol</label>
                <input type="text" name="symbol" id="symbol" placeholder="Company Symbol" required value="<?php se($company, "symbol"); ?>">
            </div>
            <div class="mb-3">
                <label for="name">Company Name</label>
                <input type="text" name="name" id="name" placeholder="Company Name" required value="<?php se($company, "name"); ?>">
            </div>
            <div class="mb-3">
                <label for="type">Company Type</label>
                <input type="text" name="type" id="type" placeholder="Company Type" required value="<?php se($company, "type"); ?>">
            </div>
            <div class="mb-3">
                <label for="region">Company Region</label>
                <input type="text" name="region" id="region" placeholder="Company Region" required value="<?php se($company, "region"); ?>">
            </div>
            <div class="mb-3">
                <label for="currency">Company Currency</label>
                <input type="text" maxlength="4" name="currency" id="currency" placeholder="Company Currency" required value="<?php se($company, "currency"); ?>">
            </div>
            <input type="submit" value="Update" class="btn btn-primary">
        </form>

</div>


<?php
//note we need to go up 1 more directory
require_once(__DIR__ . "/../../../partials/flash.php");
?>