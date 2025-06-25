<?php
//note we need to go up 1 more directory
require(__DIR__ . "/../../../partials/nav.php");

if (!has_role("Admin")) {
    flash("You don't have permission to view this page", "warning");
    die(header("Location: " . get_url("landing.php")));
}
//handle the toggle first so SELECT pulls fresh data
if (isset($_POST["role_id"])) {
    $role_id = se($_POST, "role_id", "", false);
    if (!empty($role_id)) {
        $db = getDB();
        // toggle is_active via negation
        $stmt = $db->prepare("UPDATE Roles SET is_active = !is_active WHERE id = :role_id");
        try {
            $stmt->execute([":role_id" => $role_id]);
            flash("Updated Role", "success");
        } catch (PDOException $e) {
            flash("There was an error toggling the role, please try again later", "danger");
            error_log("Error toggling role: " . var_export($e->errorInfo, true));
        }
    }
}
$query = "SELECT id, name, description, is_active from Roles";
$params = null;
if (isset($_POST["role"])) {
    $search = se($_POST, "role", "", false);
    $query .= " WHERE name LIKE :role";
    // for LIKE queries, we need to use wildcards that get added to the data rather than the query
    $params =  [":role" => "%$search%"];
}
// always apply some finite limit to avoid performance issues
$query .= " ORDER BY modified desc LIMIT 10";
$db = getDB();
$stmt = $db->prepare($query);
$roles = [];
try {
    $stmt->execute($params);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if ($results) {
        $roles = $results;
    } else {
        flash("No matches found", "warning");
    }
} catch (PDOException $e) {
    flash("There was an error fetching roles, please try again later", "danger");
    error_log("Error fetching roles: " . var_export($e->errorInfo, true));
}

?>
<h3>List Roles</h3>
<form method="POST">
    <!-- value is used to create a sticky form (maintains the data used in the initial form submission) -->
    <input type="search" name="role" placeholder="Role Filter" value="<?php se($_POST, "role"); ?>" />
    <input type="submit" value="Search" />
</form>
<small>Note: If you disabled Admin, you won't be able to login as Admin again until you re-enable it (may require a manual table edit).</small>
<table>
    <thead>
        <th>ID</th>
        <th>Name</th>
        <th>Description</th>
        <th>Active</th>
        <th>Action</th>
    </thead>
    <tbody>
        <?php if (empty($roles)) : ?>
            <tr>
                <td colspan="100%">No roles</td>
            </tr>
        <?php else : ?>
            <?php foreach ($roles as $role) : ?>
                <tr>
                    <td><?php se($role, "id"); ?></td>
                    <td><?php se($role, "name"); ?></td>
                    <td><?php se($role, "description"); ?></td>
                    <td><?php echo (se($role, "is_active", 0, false) ? "active" : "disabled"); ?></td>
                    <td>
                        <!-- nested form to handle toggling the role -->
                        <form method="POST">
                            <!-- hidden field to carry the id, the user shouldn't be prompted to edit this-->
                            <input type="hidden" name="role_id" value="<?php se($role, 'id'); ?>" />
                            <!-- used to persist the search criteria since this is a different form -->
                            <?php if (isset($search) && !empty($search)) : ?>
                                <input type="hidden" name="role" value="<?php se($search, null); ?>" />
                            <?php endif; ?>
                            <!-- toggle button to change the role's active status -->
                            <?php if (se($role, "is_active", 0, false)) : ?>
                                <input type="submit" value="Disable" />
                            <?php else : ?>
                                <input type="submit" value="Enable" />
                            <?php endif; ?>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </tbody>
</table>
<?php
//note we need to go up 1 more directory
require_once(__DIR__ . "/../../../partials/flash.php");
?>