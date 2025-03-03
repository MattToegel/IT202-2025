<?php
require(__DIR__ . "/../../partials/nav.php");
?>
<form onsubmit="return validate(this)" method="POST">
    <div>
        <label for="email">Email</label>
        <input id="email" type="email" name="email" required />
    </div>
    <div>
        <label for="pw">Password</label>
        <input type="password" id="pw" name="password" required minlength="8" />
    </div>
    <div>
        <label for="confirm">Confirm</label>
        <input type="password" name="confirm" required minlength="8" />
    </div>
    <input type="submit" value="Register" />
</form>
<script>
    function validate(form) {
        //TODO 1: implement JavaScript validation
        //ensure it returns false for an error and true for success

        return true;
    }
</script>
<?php
// TODO 2: add PHP Code
if (
    isset($_POST["email"])
    && isset($_POST["password"]) && isset($_POST["confirm"])
) {

    $email = se($_POST, "email", "", false);
    $password = se($_POST, "password", "", false);
    $confirm = se($_POST, "confirm", "", false);;
    // TODO 3: validate/use
    $hasError = false;
    // Sanitize and validate email
    $email = sanitize_email($email);
    if (!is_valid_email($email)) {
        echo "Invalid email address";
        $hasError = true;
    }
    if (empty($email)) {
        echo "Email must not be empty";
        $hasError = true;
    }

    if (empty($password)) {
        echo "Password must not be empty";
        $hasError = true;
    }

    if (empty($confirm)) {
        echo "Confirm password must not be empty";
        $hasError = true;
    }

    if (strlen($password) < 8) {
        echo "Password too short";
        $hasError = true;
    }

    if ($password !== $confirm) {
        echo "Passwords must match";
        $hasError = true;
    }

    if (!$hasError) {
        //echo "Welcome, $email";
        // TODO 4
        // comment out the "welcome" line above
        // TODO 4: Hash password before storing
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);
        $db = getDB(); // available due to the `require()` of `functions.php`
        // Code for inserting user data into the database
        $stmt = $db->prepare("INSERT INTO `S25-010-Users` (email, password) VALUES (:email, :password)");
        try {
            $stmt->execute([':email' => $email, ':password' => $hashed_password]);
            echo "Successfully registered!";
        } catch (Exception $e) {
            echo "There was an error registering<br>";
            echo "<pre>" . var_export($e, true) . "</pre>";
        }
    }
}
?>