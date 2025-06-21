<?php
require_once(__DIR__ . "/../../partials/nav.php");
if (!is_logged_in()) {
    die(header("Location: login.php"));
}
?>
<?php
$user_id = get_user_id(); // get id from session
$email = get_user_email(); // get email from session
$username = get_username(); // get username from session
// handle email/username update
if (isset($_POST["email"], $_POST["username"])) {
    $new_email = se($_POST, "email", null, false);
    $new_username = se($_POST, "username", null, false);
    $hasError = false;
    // validate format
    if (empty($new_email)) {
        //echo "Email must not be empty<br>";
        flash("Email must not be empty.", "danger");
        $hasError = true;
    }
    // Sanitize and validate email
    $new_email = sanitize_email($new_email);
    if (!is_valid_email($new_email)) {
        //echo "Invalid email address<br>";
        flash("Invalid email address.", "danger");
        $hasError = true;
    }
    if (!preg_match('/^[a-z0-9-_]{3,30}$/', $new_username)) {
        flash("Username must be lowercase, alphanumerical, and can only contain _ or -", "danger");
        $hasError = true;
    }
    // check for changes
    if (($username != $new_username || $email != $new_email) && !$hasError) {
        $saved = false;
        $params = [":email" => $new_email, ":username" => $new_username, ":id" => $user_id];
        $db = getDB();
        $stmt = $db->prepare("UPDATE Users set email = :email, username = :username where id = :id");
        try {
            $stmt->execute($params);
            $updated_rows = $stmt->rowCount();
            if ($updated_rows === 0) {
                flash("No changes made", "warning");
            } else if ($updated_rows == 1) {
                flash("Profile saved", "success");
                $saved = true;
            } else {
                // this shouldn't happen, but we log it just in case
                error_log("Unexpected number of rows updated: " . $updated_rows);
            }
        } catch (PDOException $e) {
            // handle existing email/username error
            if ($e->errorInfo[1] === 1062) {
                //https://www.php.net/manual/en/function.preg-match.php
                preg_match("/Users.(\w+)/", $e->errorInfo[2], $matches);
                if (isset($matches[1])) {
                    flash("The chosen " . $matches[1] . " is not available.", "warning");
                } else {
                    flash("Unknown error occurred", "danger");
                    error_log("Error interpreting PDOException message: " . var_export($e, true));
                }
            } else {
                flash("Unhandled error occurred", "danger");
                error_log("Error updating email/username: " . var_export($e, true));
            }
        } catch (Exception $e) {
            flash("An unexpected error occurred, please try again", "danger");
            error_log("Unexpected Error updating user details: " . var_export($e, true));
        }
        if ($saved) {
            //select fresh data from table
            $stmt = $db->prepare("SELECT email, username from Users where id = :id LIMIT 1");
            try {
                $stmt->execute([":id" => $user_id]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($user) {
                    //$_SESSION["user"] = $user; // don't overwrite the entire session data, just update the specific fields
                    $_SESSION["user"]["email"] = $user["email"];
                    $_SESSION["user"]["username"] = $user["username"];
                    // since this comes after the setting of $username and $email at the top, we'll apply the edits to them too
                    $username = $user["username"];
                    $email = $user["email"];
                } else {
                    // This shouldn't happen, but we add logs/notification just in case
                    flash("User doesn't exist", "danger");
                    error_log("User doesn't exist");
                }
            } catch (PDOException $e) {
                flash("An unexpected error occurred, please try again", "danger");
                error_log("DB Error fetching user details: " . var_export($e, true));
            } catch (Exception $e) {
                flash("An unexpected error occurred, please try again", "danger");
                error_log("Unexpected Error fetching user details: " . var_export($e, true));
            }
        }
    }
}
// handle password update
if (isset($_POST["currentPassword"], $_POST["newPassword"], $_POST["confirmPassword"])) {

    //check/update password
    $current_password = se($_POST, "currentPassword", null, false);
    $new_password = se($_POST, "newPassword", null, false);
    $confirm_password = se($_POST, "confirmPassword", null, false);
    // require all 3 to be set before attempting to process
    $can_update = !empty($current_password) && !empty($new_password) && !empty($confirm_password);
    if ($can_update) {
        // check that new matches confirm (i.e., no typos)
        if ($new_password !== $confirm_password) {
            flash("New passwords don't match", "warning");
        } else {
            //validate current password against password rules
            $hasError = false;
            if (strlen($new_password) < 8) {
                //echo "Password too short<br>";
                flash("Password must be at least 8 characters long.", "danger");
                $hasError = true;
            }
            if (!$hasError) {
                // fetch current hash
                try {
                    $db = getDB();
                    $stmt = $db->prepare("SELECT password from Users where id = :id");
                    // using get_user_id() in this block to ensure we don't mistakenly allow changing someone else's password
                    $stmt->execute([":id" => get_user_id()]);
                    $result = $stmt->fetch(PDO::FETCH_ASSOC);
                    if (isset($result["password"])) {
                        // verify current vs hash
                        if (!password_verify($current_password, $result["password"])) {
                            flash("Current password is invalid", "warning");
                        } else {
                            // change password
                            $new_hash = password_hash($new_password, PASSWORD_BCRYPT);
                            $query = "UPDATE Users set password = :password where id = :id";
                            $stmt = $db->prepare($query);
                            $stmt->execute([
                                ":id" => get_user_id(),
                                ":password" => $new_hash
                            ]);
                            $updated_rows = $stmt->rowCount();
                            if ($updated_rows === 0) {
                                flash("No changes made to password", "warning");
                            } else if ($updated_rows == 1) {
                                flash("Password updated successfully", "success");
                            } else {
                                // this shouldn't happen, but we log it just in case
                                error_log("Unexpected number of rows updated for password change: " . $updated_rows);
                            }
                        }
                    } else {
                        error_log("No password field in result");
                    }
                } catch (Exception $e) {
                    flash("Error processing password change", "danger");
                    error_log("Error processing password change: " . var_export($e, true));
                }
            }
        }
    }
}
?>
<h3>Profile</h3>
<form method="POST" onsubmit="return validate(this);">
    <div class="mb-3">
        <label for="email">Email</label>
        <input type="email" name="email" id="email" value="<?php se($email); ?>" />
    </div>
    <div class="mb-3">
        <label for="username">Username</label>
        <input type="text" name="username" id="username" value="<?php se($username); ?>" />
    </div>
    <!-- DO NOT PRELOAD PASSWORD -->
    <div>Password Reset</div>
    <div class="mb-3">
        <label for="cp">Current Password</label>
        <input type="password" name="currentPassword" id="cp" />
    </div>
    <div class="mb-3">
        <label for="np">New Password</label>
        <input type="password" name="newPassword" id="np" />
    </div>
    <div class="mb-3">
        <label for="conp">Confirm Password</label>
        <input type="password" name="confirmPassword" id="conp" />
    </div>
    <input type="submit" value="Update Profile" name="save" />
</form>

<script>
    function validate(form) {
        let pw = form.newPassword.value;
        let con = form.confirmPassword.value;
        let isValid = true;
        //TODO add other client side validation....

        //example of using flash via javascript
        //find the flash container, create a new element, appendChild
        // NOTE: we'll extract the flash code to a function later
        if (pw !== con) { // first JS validation example
            //find the container
            let flash = document.getElementById("flash");
            //create a div (or whatever wrapper we want)
            let outerDiv = document.createElement("div");
            outerDiv.className = "row justify-content-center";
            let innerDiv = document.createElement("div");

            //apply the CSS (these are bootstrap classes which we'll learn later)
            innerDiv.className = "alert alert-warning";
            //set the content
            innerDiv.innerText = "Password and Confirm password must match";

            outerDiv.appendChild(innerDiv);
            //add the element to the DOM (if we don't it merely exists in memory)
            flash.appendChild(outerDiv);
            isValid = false;
        }
        // returning false will prevent the form from submitting
        return isValid;
    }
</script>
<?php
require_once(__DIR__ . "/../../partials/flash.php");
?>