<?php
require(__DIR__ . "/../../../lib/functions.php");
session_start();
if (isset($_POST["broker_id"])) {
    $user_id = get_user_id();
    $broker_id = se($_POST, "broker_id", -1, false);
    if ($broker_id <= 0) {
        flash("Invalid broker ID", "warning");
        redirect(get_last_route());
    }
    if ($user_id <= 0) {
        flash("You must be logged in to hire a broker", "warning");
        redirect(get_last_route());
    }
    error_log("User $user_id attempting to hire broker $broker_id");
    $query = "SELECT power FROM `IT202-M25-Brokers` where id = :broker_id";
    $broker = selectAll($query, [":broker_id" => $broker_id]);
    if ($broker && count($broker) > 0) {
        $broker = $broker[0];
        $cost = get_cost($broker);
        hire($user_id, $broker_id, $cost);
    }
} else {
    flash("No broker ID provided", "warning");
}
redirect(get_last_route());
