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
if ($id < 1) {
    flash("Invalid broker", "danger");
    redirect($_SESSION["last"] ?? "brokers.php");
}

$db = getDB();
$query = "DELETE FROM `IT202-S25-Crypto` WHERE id = :id";
try {
    $stmt = $db->prepare($query);
    $stmt->bindValue(":id", $id, PDO::PARAM_INT);
    $stmt->execute();
    if ($stmt->rowCount()) {
        flash("Deleted record", "success");
    } else {
        flash("Error deleting record", "danger");
    }
} catch (PDOException $e) {
    error_log("Error deleting record: " . var_export($e, true));
    flash("Error deleting record", "danger");
}
redirect($_SESSION["last"] ?? "crypto.php");
