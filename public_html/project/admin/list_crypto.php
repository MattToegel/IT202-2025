<?php
//note we need to go up 1 more directory
require(__DIR__ . "/../../../partials/nav.php");

if (!has_role("Admin")) {
    flash("You don't have permission to view this page", "warning");
    die(header("Location: $BASE_PATH" . "/home.php"));
}



$query = "SELECT id, `currency_symbol`, `currency_name`, `average`, `date`
, is_api FROM `IT202-S25-Crypto` ORDER BY created DESC LIMIT 25";
$db = getDB();
$stmt = $db->prepare($query);
$results = [];
try {
    $stmt->execute();
    $r = $stmt->fetchAll();
    if ($r) {
        $results = $r;
    }
} catch (PDOException $e) {
    error_log("Error fetching stocks " . var_export($e, true));
    flash("Unhandled error occurred", "danger");
}
$table = ["data" => $results, "edit_url" => get_url("admin/edit_crypto.php"), "classes" => "btn btn-secondary"];
?>
<div class="container-fluid">
    <h3>List crypo</h3>
    <?php render_table($table); ?>
</div>
<?php
//note we need to go up 1 more directory
require_once(__DIR__ . "/../../../partials/footer.php");
?>