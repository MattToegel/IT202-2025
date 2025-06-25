<?php
require(__DIR__ . "/../../partials/nav.php");
?>
<h3>Login</h3>
<form onsubmit="return validate(this)" method="POST">
    <div>
        <label for="email">Email or Username</label>
        <input id="email" type="text" name="email" required />
    </div>
    <div>
        <label for="pw">Password</label>
        <input type="password" id="pw" name="password" required minlength="8" />
    </div>
    <input type="submit" value="Login" />
</form>
<script>
    function validate(form) {
        //TODO 1: implement JavaScript validation (you'll do this on your own towards the end of Milestone1)
        //ensure it returns false for an error and true for success

        return true;
    }
</script>
<?php
//TODO 2: add PHP Code
if (isset($_POST["email"], $_POST["password"])) {
    // still leveraging the property as "email", but it can be a username
    $email = se($_POST, "email", "", false);
    $password = se($_POST, "password", "", false);
    // TODO 3: validate/use
    $hasError = false;

    if (empty($email)) {
        flash("Email/Username must not be empty.", "danger");
        $hasError = true;
    }
    if (str_contains($email, "@")) {
        // if it contains an @, treat it as an email

        // Sanitize and validate email
        $email = sanitize_email($email);
        if (!is_valid_email($email)) {
            flash("Invalid email address.", "danger");
            $hasError = true;
        }
    } else {
        // otherwise, treat it as a username
        $email = strtolower(trim($email));
        if (!is_valid_username($email)) {
            flash("Username must be lowercase, alphanumerical, and can only contain _ or -", "danger");
            $hasError = true;
        }
    }


    if (empty($password)) {
        flash("Password must not be empty.", "danger");
        $hasError = true;
    }

    if (!is_valid_password($password)) {
        //echo "Password too short<br>";
        flash("Password must be at least 8 characters long.", "danger");
        $hasError = true;
    }

    if (!$hasError) {

        // TODO 4: Check password and fetch user
        if (!$hasError) {
            //TODO 4: Check password and fetch user
            $db = getDB();
            // fetch by email or username
            $stmt = $db->prepare("SELECT id, email, password, username from Users where email = :email OR username = :email");
            try {
                $r = $stmt->execute([":email" => $email]);
                if ($r) {
                    $user = $stmt->fetch(PDO::FETCH_ASSOC);
                    $ambigify = false; // flag to indicate ambiguous login attempt (reduce TMI)
                    if ($user) {
                        $hash = $user["password"];
                        unset($user["password"]);
                        if (password_verify($password, $hash)) {

                            $_SESSION["user"] = $user; // add the data to the active session
                            try {
                                //lookup potential roles
                                $stmt = $db->prepare("SELECT Roles.name FROM Roles
                                JOIN UserRoles on Roles.id = UserRoles.role_id
                                where UserRoles.user_id = :user_id and Roles.is_active = 1 
                                and UserRoles.is_active = 1");
                                $stmt->execute([":user_id" => get_user_id()]);
                                $roles = $stmt->fetchAll(PDO::FETCH_ASSOC); //fetch all since we'll want multiple
                            } catch (Exception $e) {
                                error_log(var_export($e, true));
                            }
                            //save roles or empty array
                            $_SESSION["user"]["roles"] = isset($roles) ? $roles : [];

                            die(header("Location: landing.php"));
                        } else {
                            //echo "Invalid password<br>";
                            $ambigify = true; // ambiguous login attempt
                        }
                    } else {
                        //echo "Email not found<br>";
                        $ambigify = true; // ambiguous login attempt
                    }
                    if ($ambigify) {
                        flash("Invalid login attempt. Please check your email and password.", "danger");
                    }
                }
            } catch (Exception $e) {
                //echo "There was an error logging in<br>"; // user-friendly message
                flash("There was an error logging in. Please try again later.", "danger");
                error_log("Login Error: " . var_export($e, true)); // log the technical error for debugging
            }
        }
    }
}
?>

<?php
require(__DIR__ . "/../../partials/flash.php");
?>