<?php

/**
 * Passing $redirect as true will auto redirect a logged out user to the $destination.
 * The destination defaults to login.php
 */
function is_logged_in($redirect = false, $destination = "login.php")
{
    $isLoggedIn = isset($_SESSION["user"]);
    if ($redirect && !$isLoggedIn) {
        //if this triggers, the calling script won't receive a reply since die()/exit() terminates it
        flash("You must be logged in to view this page", "warning");
        die(header("Location: $destination"));
    }
    return $isLoggedIn;
}
function has_role($role)
{
    if (is_logged_in() && isset($_SESSION["user"]["roles"])) {
        foreach ($_SESSION["user"]["roles"] as $r) {
            if ($r["name"] === $role) {
                return true;
            }
        }
    }
    return false;
}
function get_username()
{
    if (is_logged_in()) { //we need to check for login first because "user" key may not exist
        return se($_SESSION["user"], "username", "", false);
    }
    return "";
}
function get_user_email()
{
    if (is_logged_in()) { //we need to check for login first because "user" key may not exist
        return se($_SESSION["user"], "email", "", false);
    }
    return "";
}
function get_user_id()
{
    if (is_logged_in()) { //we need to check for login first because "user" key may not exist
        return se($_SESSION["user"], "id", false, false);
    }
    return false;
}
function get_my_stats()
{
    if (is_logged_in() && isset($_SESSION["user"]["stats"])) { //we need to check for login first because "user" key may not exist
        return $_SESSION["user"]["stats"];
    }
    return [];
}
function fetch_user_stats($id)
{
    $db = getDB();
    $query = "SELECT wins, losses, points FROM `IT202-S25-UserStats` WHERE user_id = :id";
    try {
        $stmt = $db->prepare($query);
        $stmt->execute([":id" => $id]);
        $r = $stmt->fetch();
        if ($r) {
            return $r;
        } else {
            //insert a new row
            $query = "INSERT INTO `IT202-S25-UserStats` (user_id) VALUES (:id)";
            try {
                $stmt = $db->prepare($query);
                $stmt->execute([":id" => $id]);
            } catch (PDOException $e) {
                error_log("Error inserting user stats " . var_export($e, true));
                // it's possible to access this function with an invalid id, so just log the error
            }
            return [
                "user_id" => $id,
                "wins" => 0,
                "losses" => 0,
                "points" => 0,
            ];
        }
    } catch (PDOException $e) {
        error_log("Error fetching user stats " . var_export($e, true));
        flash("Unhandled error occurred", "danger");
    }
    return [];
}

function change_points($id, $points)
{
    if($points == 0){
        throw new Exception("No points to change");
    }
    // db will throw a check exception if points go negative
    $db = getDB();
    $query = "UPDATE `IT202-S25-UserStats` SET points = points + :points WHERE user_id = :id";
    try {
        $stmt = $db->prepare($query);
        $stmt->execute([":id" => $id, ":points" => $points]);
    } catch (PDOException $e) {
        // handle `check` exception
        error_log("Error updating user stats " . var_export($e, true));
        if ($e->getCode() == 23000) {
            flash("You can't afford this action", "warning");
            error_log("Insufficient points ");
            throw new Exception("Insufficient points");
        }
        flash("Unhandled error occurred", "danger");
        throw new Exception("Unhandled error occurred");
    }
    // refresh session if the user is the session user
    if ($id == get_user_id()) {
        $_SESSION["user"]["stats"] = fetch_user_stats($id);
        error_log("Refreshed user's session stats" . var_export($_SESSION["user"]["stats"], true));
    }
}
