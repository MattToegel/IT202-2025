<?php
function users_check_duplicate($errorInfo)
{
    if (property_exists($errorInfo, 'errorInfo')) {
        // extract errorInfo in case a PDOException is passed
        $errorInfo = $errorInfo->errorInfo;
    }
    if( !is_array($errorInfo) || count($errorInfo) < 3) {
        flash("Unknown error occurred", "danger");
        error_log("Error interpreting PDOException message: " . var_export($errorInfo, true));
        return;
    }
    if ($errorInfo[1] === 1062) {
        //https://www.php.net/manual/en/function.preg-match.php
        //NOTE: this assumes your table name is `Users`, edit it accordingly
        preg_match("/Users.(\w+)/", $errorInfo[2], $matches);
        if (isset($matches[1])) {
            flash("The chosen " . $matches[1] . " is not available.", "warning");
        } else {
            flash("Unknown error occurred", "danger");
            error_log("Error interpreting PDOException message: " . var_export($errorInfo, true));
        }
    } else {
        flash("Unhandled error occurred", "danger");
        error_log("Error updating email/username: " . var_export($errorInfo, true));
    }
}
