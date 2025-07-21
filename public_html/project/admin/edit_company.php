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
        if (!in_array($k, ["symbol", "name", "type", "region", "currency"])) {
            unset($_POST[$k]);
        }
        $company = $_POST;
        error_log("Cleaned up POST: " . var_export($company, true));
    }
    // Ideally only the table name should need to change for most queries
    //update data
    $company["id"] = $id; // add id to the company array for the update

    try {
        $company = uppercaseSymbolCurrency([$company])[0];
        $r = update("IT202-M25-Companies", $company);
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
if ($company) {
    $keys = array_keys($company);

    foreach ($form as $k => $v) {
        if (in_array($v["name"], $keys)) {
            $form[$k]["value"] = $company[$v["name"]];
        }
    }
}
?>
<div class="container-fluid">
    <h3>Edit Company</h3>
    <form method="POST">
        <?php foreach ($form as $field): ?>
            <?php render_input($field); ?>
        <?php endforeach; ?>
        <?php render_button(["text" => "Update", "type" => "submit"]); ?>
    </form>

</div>


<?php
//note we need to go up 1 more directory
require_once(__DIR__ . "/../../../partials/footer.php");
?>