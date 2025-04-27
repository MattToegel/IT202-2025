<?php
require(__DIR__ . "/../../partials/nav.php");
/*if (is_logged_in(true)) {
    error_log("Session data: " . var_export($_SESSION, true));
}*/
$id = se($_GET, "id", -1, false);
if ($id < 1) {
    flash("Invalid crypto", "danger");
    redirect($_SESSION["last"]?? "crypto.php");
}

$params = [];
$select = "SELECT id, `currency_symbol`, `currency_name`, `average`, `date` d";
$query = " FROM `IT202-S25-Crypto`
WHERE 1=1";
$query .= " AND id = :crypto_id";
$params[":crypto_id"] = $id;

// Execute crypto query
$db = getDB();
$stmt = $db->prepare($select . $query);
error_log("crypto Query: $select.$query");
error_log("Params: " . var_export($params, true));

foreach ($params as $key => $val) {
    $type = match (true) {
        is_numeric($val) => PDO::PARAM_INT,
        is_bool($val) => PDO::PARAM_BOOL,
        is_null($val) => PDO::PARAM_NULL,
        default => PDO::PARAM_STR,
    };
    $stmt->bindValue($key, $val, $type);
}

$crypto = [];
try {
    $stmt->execute();
    $r = $stmt->fetch();
    if ($r) {
        $crypto = $r;
    }
} catch (PDOException $e) {
    error_log("Error fetching cryptos: " . var_export($e, true));
    flash("Unhandled error occurred", "danger");
}


$table = ["data"=>[$crypto]];
if(has_role("Admin")){
    $table["edit_url"] = get_url("admin/edit_crypto.php");
    $table["delete_url"] = get_url("admin/delete_crypto.php");
}
?>
<div class="container-fluid">
    <h1>Entry</h1>
    <?php if ($crypto): ?>
        <?php render_table($table) ?>
    <?php else: ?>
        <p>No crypto (big problem);</p>
    <?php endif; ?>
</div>

<?php require(__DIR__ . "/../../partials/footer.php"); ?>