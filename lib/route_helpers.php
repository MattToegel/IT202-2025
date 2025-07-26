<?php

function store_current_route() {
    $current_path = $_SERVER["REQUEST_URI"];
    if (count($_GET) > 0) {
        // add a ? to denote query parameters if its missing
        if (!str_ends_with($current_path, "?")) {
            $current_path .= "?";
        }
        // replace multiple ? with one ?
        $current_path = preg_replace('/\?.*/', '?', $current_path);
        $current_path .= http_build_query($_GET);
    } else {
        // remove ? is no query parameters
        $current_path = preg_replace('/\?.*/', '', $current_path);
    }
    error_log("Storing current route: " . $current_path);
    $_SESSION["last"] = $current_path;
}

function get_last_route($isEcho = false, $default = "brokers.php") {
    return get_url(isset($_SESSION["last"]) ? $_SESSION["last"] : $default, $isEcho);
}
