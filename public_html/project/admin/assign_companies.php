<?php
//note we need to go up 1 more directory
require(__DIR__ . "/../../../partials/nav.php");

if (!has_role("Admin")) {
    flash("You don't have permission to view this page", "warning");
    die(header("Location: $BASE_PATH" . "/home.php"));
}
//attempt to apply
if (isset($_POST["users"]) && isset($_POST["companies"])) {
    $user_ids = $_POST["users"]; //se() doesn't like arrays so we'll just do this
    $company_ids = $_POST["companies"]; //se() doesn't like arrays so we'll just do this
    if (empty($user_ids) || empty($company_ids)) {
        flash("Both users and companies need to be selected", "warning");
    } else {
        //for sake of simplicity, this will be a tad inefficient
        $db = getDB();
        $stmt = $db->prepare("INSERT INTO `IT202-S25-UserCompany` (user_id, company_id, is_active) VALUES (:uid, :cid, 1) 
        ON DUPLICATE KEY UPDATE is_active = !is_active");
        foreach ($user_ids as $uid) {
            foreach ($company_ids as $cid) {
                try {
                    $stmt->execute([":uid" => $uid, ":cid" => $cid]);
                    flash("Updated Association", "success");
                } catch (PDOException $e) {
                    flash(var_export($e->errorInfo, true), "danger");
                }
            }
        }
    }
}

//get active companies
$active_companies = [];
$company = se($_POST, "company", "", false);
if (!empty($company)) {
    $db = getDB();
    $stmt = $db->prepare("SELECT id, name, symbol FROM `IT202-S25-Companies` WHERE  name like :company LIMIT 10");
    try {
        $stmt->execute([":company" => "%$company%"]);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if ($results) {
            $active_companies = $results;
        }
    } catch (PDOException $e) {
        flash(var_export($e->errorInfo, true), "danger");
    }
}


//search for user by username
$users = [];
if (isset($_POST["username"])) {
    $username = se($_POST, "username", "", false);
    if (!empty($username)) {
        $db = getDB();
        $stmt = $db->prepare("SELECT Users.id, username, 
        (SELECT GROUP_CONCAT(name, ' (' , symbol , ')') from 
        `IT202-S25-UserCompany` uc JOIN `IT202-S25-Companies` c on uc.company_id = c.id WHERE uc.user_id = Users.id) as companies
        from Users WHERE username like :username");
        try {
            $stmt->execute([":username" => "%$username%"]);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if ($results) {
                $users = $results;
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
    <h1>Assign companies</h1>
    <form method="POST">
        <div class="mb-3">
            <?php render_input(["type" => "text", "name" => "username", "id" => "username", "label" => "Username search", "rules" => ["required" => true]]); ?>
        </div>
        <div class="mb-3">
            <?php render_input(["type" => "text", "name" => "company", "id" => "company", "label" => "Company search", "rules" => ["required" => true]]); ?>
        </div>
        <input type="hidden" name="action" value="fetch">
        <?php render_button(["text" => "Search", "type" => "submit"]); ?>
    </form>
    <form method="POST">
        <?php if (isset($username) && !empty($username) && isset($company) && !empty($company)) : ?>
            <input type="hidden" name="username" value="<?php se($username, false); ?>" />
            <input type="hidden" name="company" value="<?php se($company, false); ?>" />
        <?php endif; ?>
        <table class="table">
            <thead>
                <th>Users</th>
                <th>companies to Assign</th>
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
                                    <td><?php se($user, "companies", "No companies"); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </table class="table">
                    </td>
                    <td>
                        <?php foreach ($active_companies as $company) : ?>
                            <div>
                                <label for="company_<?php se($company, 'id'); ?>"><?php se($company, "name"); ?></label>
                                <input id="company_<?php se($company, 'id'); ?>" type="checkbox" name="companies[]" value="<?php se($company, 'id'); ?>" />
                            </div>
                        <?php endforeach; ?>
                    </td>
                </tr>
            </tbody>
        </table>
        <input type="submit" value="Toggle Companies" class="btn btn-primary" for="toggle_companies" />
    </form>
</div>
<?php
//note we need to go up 1 more directory
require_once(__DIR__ . "/../../../partials/footer.php");
?>