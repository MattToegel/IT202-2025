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
        echo "Email must not be empty<br>";
        $hasError = true;
    }
    // Sanitize and validate email
    $email = filter_var($email, FILTER_SANITIZE_EMAIL);
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "Invalid email address";
        $hasError = true;
    }
    if (empty($password)) {
        echo "Password must not be empty<br>";
        $hasError = true;
    }

    if (strlen($password) < 8) {
        echo "Password too short<br>";
        $hasError = true;
    }

    if (!$hasError) {

        // TODO 4: Check password and fetch user
        if (!$hasError) {
            //TODO 4: Check password and fetch user
            $db = getDB();
            $stmt = $db->prepare("SELECT id, email, password from Users where email = :email");
            try {
                $r = $stmt->execute([":email" => $email]);
                if ($r) {
                    $user = $stmt->fetch(PDO::FETCH_ASSOC);
                    if ($user) {
                        $hash = $user["password"];
                        unset($user["password"]);
                        if (password_verify($password, $hash)) {
                            echo "Welcome, $email!<br>";
                        } else {
                            echo "Invalid password<br>";
                        }
                    } else {
                        echo "Email not found<br>";
                    }
                }
            } catch (Exception $e) {
                echo "There was an error logging in<br>"; // user-friendly message
                error_log("Login Error: " . var_export($e, true)); // log the technical error for debugging
            }
        }
    }
}
?>