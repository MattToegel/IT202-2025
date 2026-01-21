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
if (isset($_POST["average"])) {
    foreach ($_POST as $k => $v) {
        if (!in_array($k, [ "average", "date"])) {
            unset($_POST[$k]);
        }
        $crypto = $_POST;
        error_log("Cleaned up POST: " . var_export($crypto, true));
    }
    // Ideally only the table name should need to change for most queries
    //update data
    $crypto["id"] = $id; // add id to the company array for the update
    try {
        $r = update("IT202-S25-Crypto", $crypto);
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

$crypto = [];
if ($id > -1) {
    //fetch
    $db = getDB();
    $query = "SELECT currency_symbol, currency_name, `average`, `date` FROM `IT202-S25-Crypto` WHERE id = :id";
    try {
        $stmt = $db->prepare($query);
        $stmt->execute([":id" => $id]);
        $r = $stmt->fetch();
        if ($r) {
            $crypto = $r;
        }
    } catch (PDOException $e) {
        error_log("Error fetching record: " . var_export($e, true));
        flash("Error fetching record", "danger");
    }
} else {
    flash("Invalid id passed", "danger");
    die(header("Location:" . get_url("admin/list_crypto.php")));
}
$form = [
    [
        "type" => "text",
        "id" => "currency_symbol",
        "name" => "currency_symbol",
        "label" => "currency_symbol Symbol",
        "rules" => ["required" => true, "disabled"=> true]
    ],
    [
        "type" => "text",
        "id" => "currency_name",
        "name" => "currency_name",
        "label" => "Crypto Name",
        "rules" => ["required" => true, "disabled"=> true]
    ],
    [
        "type" => "number",
        "id" => "average",
        "name" => "average",
        "label" => "Average",
        "rules" => ["required" => true, "step"=>.0001]
    ],
    [
        "type" => "date",
        "id" => "date",
        "name" => "date",
        "label" => "Date",
        "rules" => ["required" => true]
    ]
];
if ($crypto) {
    $keys = array_keys($crypto);

    foreach ($form as $k => $v) {
        if (in_array($v["name"], $keys)) {
            $form[$k]["value"] = $crypto[$v["name"]];
        }
    }
}
?>
<div class="container-fluid">
    <h3>Edit Crypto</h3>
    <form method="POST">
        <?php foreach ($form as $field): ?>
            <div class="mb-3">
                <?php render_input($field); ?>
            </div>
        <?php endforeach; ?>
        <?php render_button(["text" => "Update", "type" => "submit"]); ?>
    </form>

</div>


<?php
//note we need to go up 1 more directory
require_once(__DIR__ . "/../../../partials/footer.php");
?>