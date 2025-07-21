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
    if (!is_valid_username($new_username)) {
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
            users_check_duplicate($e);
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
        if (!is_valid_confirm($new_password, $confirm_password)) {
            flash("New passwords don't match", "warning");
        } else {
            //validate current password against password rules
            $hasError = false;
            if (!is_valid_password($new_password)) {
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
// represent form as data
$form = [
    [
        "type" => "email",
        "id" => "email",
        "name" => "email",
        "label" => "Email",
        "value" => se($email, null, "", false),
        "rules" => ["required" => true]
    ],
    [
        "type" => "text",
        "id" => "username",
        "name" => "username",
        "label" => "Username",
        "value" => se($username, null, "", false),
        "rules" => ["required" => true]
    ],
    // Password reset section
    [
        "type" => "password",
        "id" => "cp",
        "name" => "currentPassword",
        "label" => "Current Password",
        "rules" => ["minlength" => 8]
    ],
    [
        "type" => "password",
        "id" => "np",
        "name" => "newPassword",
        "label" => "New Password",
        "rules" => ["minlength" => 8]
    ],
    [
        "type" => "password",
        "id" => "conp",
        "name" => "confirmPassword",
        "label" => "Confirm Password",
        "rules" => ["minlength" => 8]
    ]
];
?>
<div class="container-fluid">
    <h3>Profile</h3>
    <form method="POST" onsubmit="return validate(this);">
        <?php foreach ($form as $field): ?>
            <div class="mb-3">
                <?php render_input($field); ?>
            </div>
        <?php endforeach; ?>
        <?php render_button(["text" => "Update Profile", "type" => "submit"]); ?>
    </form>

    <script>
        function validate(form) {
            let pw = form.newPassword.value;
            let con = form.confirmPassword.value;
            let cp = form.currentPassword.value;
            let isValid = true;
            //TODO add other client side validation....

            //example of using flash via javascript
            //find the flash container, create a new element, appendChild
            if (pw && con && cp) {
                if (!isValidPassword(pw)) {
                    isValid = false;
                    flash("New Password must be at least 8 characters long", "danger");
                }
                if (!isValidPassword(con)) {
                    isValid = false;
                    flash("Confirm Password must be at least 8 characters long", "danger");
                }
                if (!isValidPassword(cp)) {
                    isValid = false;
                    flash("Current Password must be at least 8 characters long", "danger");
                }
                if (pw !== con) {
                    flash("Password and Confirm password must match", "warning");
                    isValid = false;
                }
            }

            return isValid;
        }
    </script>
</div>
<?php
require_once(__DIR__ . "/../../partials/footer.php");
?>