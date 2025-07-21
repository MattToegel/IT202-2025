<?php

/**
 * Check if the user is logged in and optionally redirect to $destination.
 * @param bool $redirect Whether to redirect if not logged in.
 * @param string $destination The destination to redirect to if not logged in (relative to BASE_PATH or absolute).
 * @return bool True if the user is logged in, false otherwise.
 */
function is_logged_in($redirect = false, $destination = "login.php") {
    $isLoggedIn = isset($_SESSION["user"]);
    if ($redirect && !$isLoggedIn) {
        //if this triggers, the calling script won't receive a reply since die()/exit() terminates it
        flash("You must be logged in to view this page", "warning");
        $path = get_url($destination);

        die(header("Location: $path"));
    }
    return $isLoggedIn;
}
function has_role($role) {
    if (is_logged_in() && isset($_SESSION["user"]["roles"])) {
        foreach ($_SESSION["user"]["roles"] as $r) {
            if ($r["name"] === $role) {
                return true;
            }
        }
    }
    return false;
}
function get_username() {
    if (is_logged_in()) { //we need to check for login first because "user" key may not exist
        return se($_SESSION["user"], "username", "", false);
    }
    return "";
}
function get_user_email() {
    if (is_logged_in()) { //we need to check for login first because "user" key may not exist
        return se($_SESSION["user"], "email", "", false);
    }
    return "";
}
function get_user_id() {
    if (is_logged_in()) { //we need to check for login first because "user" key may not exist
        return se($_SESSION["user"], "id", false, false);
    }
    return -1;
}

const _default_stats = [
    "wins" => 0,
    "losses" => 0,
    "points" => 0,
    "brokers" => 0
];
function get_my_stats() {
    if (is_logged_in() && isset($_SESSION["user"]["stats"])) { //we need to check for login first because "user" key may not exist
        return $_SESSION["user"]["stats"];
    }
    // default empty data in same expected shape
    return _default_stats;
}
function fetch_user_stats($id) {
    if (!$id || $id <= 0) {
        return _default_stats;
    }
    $db = getDB();
    $query = "SELECT wins, losses, points, user_id FROM `IT202-M25-UserStats` WHERE user_id = :id";
    try {
        $stmt = $db->prepare($query);
        $stmt->execute([":id" => $id]);
        $r = $stmt->fetch();
        if ($r) {
            return array_merge(_default_stats, $r);
        } else {
            //insert a new row
            $query = "INSERT INTO `IT202-M25-UserStats` (user_id) VALUES (:id)";
            try {
                $stmt = $db->prepare($query);
                $stmt->execute([":id" => $id]);
            } catch (PDOException $e) {
                error_log("Error inserting user stats " . var_export($e, true));
                // it's possible to access this function with an invalid id, so just log the error
            }
            $response = array_merge(_default_stats, ["user_id" => $id]);
            return $response;
        }
    } catch (PDOException $e) {
        error_log("Error fetching user stats " . var_export($e, true));
        flash("Unhandled error occurred", "danger");
    }
    return _default_stats;
}

function change_points($id, $points) {
    if ($points == 0) {
        throw new Exception("No points to change");
    }
    // db will throw a check exception if points go negative
    $db = getDB();
    $query = "UPDATE `IT202-M25-UserStats` SET points = points + :points WHERE user_id = :id";
    try {
        $stmt = $db->prepare($query);
        $stmt->execute([":id" => $id, ":points" => $points]);
    } catch (PDOException $e) {
        // handle `check` exception
        error_log("Error updating user stats " . var_export($e, true));

        if ($e->errorInfo[1] == 3819) {
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
