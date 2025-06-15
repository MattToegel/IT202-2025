<?php
require(__DIR__."/../../lib/functions.php");
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
        //TODO 1: implement JavaScript validation (you'll do this on your own towards the end of Milestone1)
        //ensure it returns false for an error and true for success

        return true;
    }
</script>
<?php
 //TODO 2: add PHP Code
 if (isset($_POST["email"], $_POST["password"], $_POST["confirm"])) {

    $email = se($_POST, "email", "", false); 
    $password = se($_POST, "password", "", false);
    $confirm = se($_POST, "confirm", "", false);
    // TODO 3: validate/use
    $hasError = false;

    if (empty($email)) {
        echo "Email must not be empty<br>";
        $hasError = true;
    }
    // Sanitize and validate email
    $email = filter_var($email, FILTER_SANITIZE_EMAIL);
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "Invalid email address<br>";
        $hasError = true;
    }
    if (empty($password)) {
        echo "Password must not be empty<br>";
        $hasError = true;
    }

    if (empty($confirm)) {
        echo "Confirm password must not be empty<br>";
        $hasError = true;
    }

    if (strlen($password) < 8) {
        echo "Password too short<br>";
        $hasError = true;
    }

    if ($password !== $confirm) {
        echo "Passwords must match<br>";
        $hasError = true;
    }

    if (!$hasError) {
        //echo "Success<br>";
        // TODO 4: Hash password and store record in DB
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);
        $db = getDB(); // available due to the `require()` of `functions.php`
        // Code for inserting user data into the database
        $stmt = $db->prepare("INSERT INTO Users (email, password) VALUES (:email, :password)");
        try{
            $stmt->execute([':email' => $email, ':password' => $hashed_password]);
            echo "Successfully registered!<br>";
        }
        catch(Exception $e){
            echo "There was an error registering<br>"; // user-friendly message
            error_log("Registration Error: " . var_export($e, true)); // log the technical error for debugging
        }
    }
}
?>