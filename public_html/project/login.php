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
    <input type="submit" value="Login" />
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
    && isset($_POST["password"])) {

    $email = se($_POST, "email", "", false);
    $password = se($_POST, "password", "", false);
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


    if (strlen($password) < 8) {
        echo "Password too short";
        $hasError = true;
    }

    if (!$hasError) {
        //TODO 4
        $db = getDB();
        $stmt = $db->prepare("SELECT id, email, password from `S25-010-Users` where email = :email");
        try {
            $r = $stmt->execute([":email" => $email]);
            if ($r) {
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($user) {
                    $hash = $user["password"];
                    unset($user["password"]);
                    if (password_verify($password, $hash)) {
                        //echo "Welcome $email";
                        $_SESSION["user"] = $user;
                        die(header("Location: home.php"));
                    } else {
                        echo "Invalid password";
                    }
                } else {
                    echo "Email not found";
                }
            }
        } catch (Exception $e) {
            echo "An error occurred: " . $e->getMessage();
        }
    }
}
?>