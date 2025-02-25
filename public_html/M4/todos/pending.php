<?php
require_once(__DIR__ . "/../../../lib/db.php"); ?>

<?php
$db = getDB();
// process complete action
if (isset($_POST["id"])) {
    $id = $_POST["id"];
    /*
    Create a query that'll update the respective ToDo marking it complete and setting the date for the completed date field as today.
    Ensure the "id" is utilized using proper PDO named placeholders so that only the one item is updated.
    Add an extra clause to update only if the complete field of the record is not set.
    https://phpdelusions.net/pdo
    */
    $query = ""; // edit this
    $params = []; // apply mapping
    
    try {
        $stmt = $db->prepare($query);
        $r = $stmt->execute($params);
        if ($r) {
            echo "Marked task $id as completed";
        } else {
            echo "Failed to mark task $id as completed";
        }
    } catch (PDOException $e) {
        echo "Error updating task $id; check the logs (terminal)";
        error_log("Update Error: " . var_export($e, true)); // shows in the terminal
    }
}
/* Refer to the HTML table below and build a query that'll select the columns in the same order as the table from the Todo table.
Cross-reference the HTML table columns with what'd most plausibly match the SQL table aside from the notes below.
For the Status part, you'll need to calculate the "days_offset" from the due date, ensure the virtual column matches "days_offset".
For Actions, this isn't part of the query and there's nothing special to select for it.
Filter the results where the todo item is NOT completed and order the results by those due the soonest.
No limit is required.
*/
$query = ""; // edit this
$results = [];
try {
    $stmt = $db->prepare($query);
    $r = $stmt->execute();
    if ($r) {
        $results = $stmt->fetchAll();
    }
} catch (PDOException $e) {
    echo "Error fetching pending todos; check the logs (terminal)";
    error_log("Select Error: " . var_export($e, true)); // shows in the terminal
}
?>
<html>

<body>
    <?php require_once(__DIR__ . "/../nav.php"); ?>
    <section>
        <h2>Pending ToDos</h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Task</th>
                    <th>Due Date</th>
                    <th>Status</th>
                    <th>Assigned</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($results as $r): ?>
                    <tr>
                        <?php foreach ($r as $key => $val): ?>
                            <?php if ($key == "days_offset"): ?>
                                <?php if ($val >= 0): ?>
                                    <td><?php echo "Due in $val day(s)"; ?></td>
                                <?php else: ?>
                                    <td><?php echo "Overdue by " . abs($val) . " day(s)"; ?></td>
                                <?php endif; ?>

                            <?php else: ?>
                                <td><?php echo $val; ?></td>
                            <?php endif; ?>
                        <?php endforeach; ?>
                        <td>
                            <form method="POST">
                                <input type="hidden" name="id" value="<?php echo $r['id']; ?>" />
                                <input type="submit" value="Complete" />
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (count($results) === 0): ?>
                    <tr>
                        <td colspan="100%">No results</td>

                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </section>
</body>

</html>