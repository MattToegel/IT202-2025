<?php
//note we need to go up 1 more directory
require(__DIR__ . "/../../../lib/functions.php");
session_start();

if (!has_role("Admin")) {
    flash("You don't have permission to view this page", "warning");
    die(header("Location: " . get_url("landing.php")));
}
$broker_id = se($_GET, "id", -1, false);
if ($broker_id < 0) {
    flash("Invalid broker id", "danger");
    redirect(get_last_route());
}
$query = "UPDATE `IT202-M25-Brokers` SET is_active = 0 WHERE id = :id";
$db = getDB();
$stmt = $db->prepare($query);
try {
    $stmt->execute([":id" => $broker_id]);
    if ($stmt->rowCount() > 0) {
        flash("Successfully deleted broker with id $broker_id", "success");
    } else {
        flash("No changes made, broker may not exist or already deleted", "warning");
    }
} catch (PDOException $e) {
    flash("There was an error deleting the broker, please try again later", "danger");
    error_log("Error deleting broker with id $broker_id: " . var_export($e->errorInfo, true));
}
redirect(get_last_route());
