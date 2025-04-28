<?php
//note we need to go up 1 more directory
require(__DIR__ . "/../../../partials/nav.php");

if (!has_role("Admin")) {
    flash("You don't have permission to view this page", "warning");
    die(header("Location: $BASE_PATH" . "/home.php"));
}
//attempt to apply
if (isset($_POST["users"]) && isset($_POST["crypto"])) {
    $user_ids = $_POST["users"]; //se() doesn't like arrays so we'll just do this
    $crypto_ids = $_POST["crypto"]; //se() doesn't like arrays so we'll just do this
    if (empty($user_ids) || empty($crypto_ids)) {
        flash("Both users and crypto need to be selected", "warning");
    } else {
        //for sake of simplicity, this will be a tad inefficient
        $db = getDB();
        $stmt = $db->prepare("INSERT INTO `IT202-S25-UserCrypto` (user_id, crypto_id) VALUES (:uid, :rid)");
        foreach ($user_ids as $uid) {
            foreach ($crypto_ids as $rid) {
                try {
                    $stmt->execute([":uid" => $uid, ":rid" => $rid]);
                    flash("Updated role", "success");
                } catch (PDOException $e) {
                    try{
                        $db2 = getDB();
                        $del_query = "DELETE FROM `IT202-S25-UserCrypto` WHERE user_id = :user_id AND crypto_id = :crypto_id";
                        $stmt2 = $db2->prepare($del_query);
                        $stmt2->execute([":user_id" => $uid, ":crypto_id" => $rid]);
                    }
                    catch(PDOException $e2){
                        flash(var_export($e2->errorInfo, true), "danger");
                    }
                    
                }
            }
        }
    }
}
$users = [];
$crypto = [];
error_log("handling fetch " . var_export($_POST, true));
if (isset($_POST["action"])) {
   
    $db = getDB();
    $stmt = $db->prepare("SELECT id, currency_name, currency_symbol, `date` FROM `IT202-S25-Crypto` WHERE currency_name like :name LIMIT 25");
    try {
        $name = $_POST["name"];
        $stmt->execute([":name" => "%$name%"]);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if ($results) {
            $crypto = $results;
            error_log("Crypto\n" . var_export($crypto, true));
        }
    } catch (PDOException $e) {
        flash(var_export($e->errorInfo, true), "danger");
    }
    $username = se($_POST, "username", "", false);
    if (!empty($username)) {
        $db = getDB();
        $stmt = $db->prepare("SELECT Users.id, username, 
        (SELECT GROUP_CONCAT(crypto.currency_symbol, crypto.date) from 
        `IT202-S25-UserCrypto` ur JOIN `IT202-S25-Crypto` crypto on ur.crypto_id = crypto.id WHERE ur.user_id = Users.id) as crypto
        from Users WHERE username like :username LIMIT 25");
        try {
            $stmt->execute([":username" => "%$username%"]);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if ($results) {
                $users = $results;
                error_log("Users\n" . var_export($users, true));
            }
        } catch (PDOException $e) {
            flash(var_export($e->errorInfo, true), "danger");
        }
    } else {
        flash("Username must not be empty", "warning");
    }
}

?>
<div class="container-fluid">
    <h1>Assign crypto</h1>
    <form method="POST">
        <div class="mb-3">
            <?php render_input(["type" => "text", "name" => "username", "id" => "username", "label" => "Username search", "rules" => ["required" => true]]); ?>
            <?php render_input(["type" => "text", "name" => "name", "id" => "name", "label" => "Crypto Name", "rules" => ["required" => true]]); ?>
        </div>
        <input type="hidden" name="action" value="fetch">
        <?php render_button(["text" => "Search", "type" => "submit"]); ?>
    </form>
    <form method="POST">
        <?php if (isset($username) && !empty($username)) : ?>
            <input type="hidden" name="username" value="<?php se($username, false); ?>" />
        <?php endif; ?>
        <table class="table">
            <thead>
                <th>Users</th>
                <th>crypto to Assign</th>
            </thead>
            <tbody>
                <tr>
                    <td>
                        <table class="table">
                            <?php foreach ($users as $user) : ?>
                                <tr>
                                    <td>
                                        <label for="user_<?php se($user, 'id'); ?>"><?php se($user, "username"); ?></label>
                                        <input id="user_<?php se($user, 'id'); ?>" type="checkbox" name="users[]" value="<?php se($user, 'id'); ?>" />
                                    </td>
                                    <td><?php se($user, "crypto", "No crypto"); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </table class="table">
                    </td>
                    <td>
                        <?php foreach ($crypto as $c) : ?>
                            <div>
                                <label for="role_<?php se($c, 'id'); ?>"><?php se($c, "currency_name"); ?> (<?php se($c,"date");?>)</label>
                                <input id="role_<?php se($c, 'id'); ?>" type="checkbox" name="crypto[]" value="<?php se($c, 'id'); ?>" />
                            </div>
                        <?php endforeach; ?>
                    </td>
                </tr>
            </tbody>
        </table>
        <?php render_button(["text" => "Toggle crypto", "type" => "submit"]); ?>
    </form>
</div>
<?php
//note we need to go up 1 more directory
require_once(__DIR__ . "/../../../partials/footer.php");
?>