<?php
require(__DIR__ . "/../../partials/nav.php");
?>
<h3>Login</h3>
<form onsubmit="return validate(this)" method="POST">
    <div>
        <label for="email">Email</label>
        <input id="email" type="email" name="email" required />
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

    $email = se($_POST, "email", "", false);
    $password = se($_POST, "password", "", false);
    // TODO 3: validate/use
    $hasError = false;

    if (empty($email)) {
        //echo "Email must not be empty<br>";
        flash("Email must not be empty.", "danger");
        $hasError = true;
    }
    // Sanitize and validate email
    $email = sanitize_email($email);
    if (!is_valid_email($email)) {
        //echo "Invalid email address";
        flash("Invalid email address.", "danger");
        $hasError = true;
    }
    if (empty($password)) {
        //echo "Password must not be empty<br>";
        flash("Password must not be empty.", "danger");
        $hasError = true;
    }

    if (strlen($password) < 8) {
        //echo "Password too short<br>";
        flash("Password must be at least 8 characters long.", "danger");
        $hasError = true;
    }

    if (!$hasError) {

        // TODO 4: Check password and fetch user
        if (!$hasError) {
            //TODO 4: Check password and fetch user
            $db = getDB();
            $stmt = $db->prepare("SELECT id, email, password, username from Users where email = :email");
            try {
                $r = $stmt->execute([":email" => $email]);
                if ($r) {
                    $user = $stmt->fetch(PDO::FETCH_ASSOC);
                    $ambigify = false; // flag to indicate ambiguous login attempt (reduce TMI)
                    if ($user) {
                        $hash = $user["password"];
                        unset($user["password"]);
                        if (password_verify($password, $hash)) {
                            //echo "Welcome, $email!<br>";
                            $_SESSION["user"] = $user; // add the data to the active session
                            die(header("Location: landing.php"));
                        } else {
                            //echo "Invalid password<br>";
                            $ambigify = true; // ambiguous login attempt
                        }
                    } else {
                        //echo "Email not found<br>";
                        $ambigify = true; // ambiguous login attempt
                    }
                    if($ambigify) {
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
require(__DIR__."/../../partials/flash.php");
?>