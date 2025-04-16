<?php
require_once(__DIR__ . "/../../partials/nav.php");
is_logged_in(true);
?>
<?php
if (isset($_POST["save"])) {
    $email = se($_POST, "email", null, false);
    $username = se($_POST, "username", null, false);
    if (!is_valid_email($email)) {
        flash("Invalid email address", "danger");
        $hasError = true;
    }
    if (!is_valid_username($username)) {
        flash("Username must only contain 3-16 characters a-z, 0-9, _, or -", "danger");
        $hasError = true;
    }
    if (!$hasError) {
        $params = [":email" => $email, ":username" => $username, ":id" => get_user_id()];
        $db = getDB();
        $stmt = $db->prepare("UPDATE Users set email = :email, username = :username where id = :id");
        try {
            $stmt->execute($params);
            flash("Profile saved", "success");
        } catch (PDOException $e) {
            if ($e->errorInfo[1] === 1062) {
                //https://www.php.net/manual/en/function.preg-match.php
                preg_match("/Users.(\w+)/", $e->errorInfo[2], $matches);
                if (isset($matches[1])) {
                    flash("The chosen " . $matches[1] . " is not available.", "warning");
                } else {
                    //TODO come up with a nice error message
                    echo "<pre>" . var_export($e->errorInfo, true) . "</pre>";
                }
            } else {
                //TODO come up with a nice error message
                echo "<pre>" . var_export($e->errorInfo, true) . "</pre>";
            }
        }
    }
    //select fresh data from table
    $stmt = $db->prepare("SELECT id, email, username from Users where id = :id LIMIT 1");
    try {
        $stmt->execute([":id" => get_user_id()]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($user) {
            //$_SESSION["user"] = $user;
            $_SESSION["user"]["email"] = $user["email"];
            $_SESSION["user"]["username"] = $user["username"];
        } else {
            flash("User doesn't exist", "danger");
        }
    } catch (Exception $e) {
        flash("An unexpected error occurred, please try again", "danger");
        //echo "<pre>" . var_export($e->errorInfo, true) . "</pre>";
    }


    //check/update password
    $current_password = se($_POST, "currentPassword", null, false);
    $new_password = se($_POST, "newPassword", null, false);
    $confirm_password = se($_POST, "confirmPassword", null, false);
    if (!empty($current_password) && !empty($new_password) && !empty($confirm_password)) {
        if ($new_password === $confirm_password) {
            $isValid = true;
            if (!is_valid_password($new_password)) {
                flash("New Password too short", "danger");
                $isValid = false;
            }
            if ($isValid) {
                //TODO validate current
                $stmt = $db->prepare("SELECT password from Users where id = :id");
                try {
                    $stmt->execute([":id" => get_user_id()]);
                    $result = $stmt->fetch(PDO::FETCH_ASSOC);
                    if (isset($result["password"])) {
                        if (password_verify($current_password, $result["password"])) {
                            $query = "UPDATE Users set password = :password where id = :id";
                            $stmt = $db->prepare($query);
                            $stmt->execute([
                                ":id" => get_user_id(),
                                ":password" => password_hash($new_password, PASSWORD_BCRYPT)
                            ]);

                            flash("Password reset", "success");
                        } else {
                            flash("Current password is invalid", "warning");
                        }
                    }
                } catch (PDOException $e) {
                    echo "<pre>" . var_export($e->errorInfo, true) . "</pre>";
                }
            }
        } else {
            flash("New passwords don't match", "warning");
        }
    }
}
?>

<?php
$email = get_user_email();
$username = get_username();

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
                    flash("Password and Confrim password must match", "warning");
                    isValid = false;
                }
            }

            return isValid;
        }
    </script>
</div>
<?php
require_once(__DIR__ . "/../../partials/flash.php");
?>