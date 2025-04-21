<?php
require(__DIR__ . "/../../partials/nav.php");
reset_session();

// represent form as data
$form = [
    ["type" => "email", "id" => "email", "name" => "email", "label" => "Email", "rules" => ["required" => true]],
    [
        "type" => "text",
        "id" => "username",
        "name" => "username",
        "label" => "Username",
        "rules" => [
            "required" => true,
            "maxlength" => 30,
            "title" => "3-16 lowercase letters, numbers, underscores, or hyphens"
        ]
    ],
    ["type" => "password", "id" => "password", "name" => "password", "label" => "Password", "rules" => ["required" => true, "minlength" => 8]],
    ["type" => "password", "id" => "confirm", "name" => "confirm", "label" => "Confirm Password", "rules" => ["required" => true, "minlength" => 8]],
];
?>
<div class="container-fluid">
    <h3>Regiser</h3>
    <form onsubmit="return validate(this)" method="POST">
        <?php foreach ($form as $field): ?>
            <div class="mb-3">
                <?php render_input($field); ?>
            </div>
        <?php endforeach; ?>
        <?php render_button(["text" => "Register", "type" => "submit"]); ?>
    </form>
</div>
<script>
    function validate(form) {
        //TODO 1: implement JavaScript validation
        //ensure it returns false for an error and true for success
        let isValid = true;
        if (!isValidPassword(form.password.value)) {
            isValid = false;
            flash("Password must be at least 8 characters long", "danger");
        }
        return isValid;
    }
</script>
<?php
//TODO 2: add PHP Code
if (isset($_POST["email"]) && isset($_POST["password"]) && isset($_POST["confirm"]) && isset($_POST["username"])) {
    $email = se($_POST, "email", "", false);
    $password = se($_POST, "password", "", false);
    $confirm = se($_POST, "confirm", "", false);
    $username = se($_POST, "username", "", false);
    //TODO 3
    $hasError = false;
    if (empty($email)) {
        flash("Email must not be empty", "danger");
        $hasError = true;
    }
    //sanitize
    $email = sanitize_email($email);
    //validate
    if (!is_valid_email($email)) {
        flash("Invalid email address", "danger");
        $hasError = true;
    }
    if (!is_valid_username($username)) {
        flash("Username must only contain 3-16 characters a-z, 0-9, _, or -", "danger");
        $hasError = true;
    }
    if (empty($password)) {
        flash("password must not be empty", "danger");
        $hasError = true;
    }
    if (empty($confirm)) {
        flash("Confirm password must not be empty", "danger");
        $hasError = true;
    }
    if (!is_valid_password($password)) {
        flash("Password too short", "danger");
        $hasError = true;
    }
    if (
        strlen($password) > 0 && $password !== $confirm
    ) {
        flash("Passwords must match", "danger");
        $hasError = true;
    }
    if (!$hasError) {
        //TODO 4
        $hash = password_hash($password, PASSWORD_BCRYPT);
        $db = getDB();
        $stmt = $db->prepare("INSERT INTO Users (email, password, username) VALUES(:email, :password, :username)");
        try {
            $stmt->execute([":email" => $email, ":password" => $hash, ":username" => $username]);
            $id = $db->lastInsertId();
            if($id){
                // welcome bonus points
                try{
                    change_points($id, 10);
                    flash("Gained a welcome bonus of 10 points!", "success");
                }
                catch(Exception $e){
                    error_log("Error adding points " . var_export($e, true));
                }
            }
            flash("Successfully registered!", "success");
        } catch (PDOException $e) {
            users_check_duplicate($e->errorInfo);
        }
    }
}
?>
<?php
require(__DIR__ . "/../../partials/footer.php");
?>