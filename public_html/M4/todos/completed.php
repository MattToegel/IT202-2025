<?php
require_once(__DIR__ . "/../../../lib/db.php"); ?>

<?php
$db = getDB();

/* Refer to the HTML table below and build a query that'll select the columns in the same order as the table from the Todo table.
Cross-reference the HTML table columns with what'd most plausibly match the SQL table aside from the notes below.
For the completed date you'll need to extract the date portion from the completed column.
For the Status part, you'll need to calculate the "days_offset" from the completed date, ensure the virtual column matches "days_offset".
Filter the results where the todo item is completed and order the results by most recently completed and most recently due.
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
        <h2>Completed ToDos</h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Task</th>
                    <th>Due Date</th>
                    <th>Completed Date</th>
                    <th>Status</th>
                    <th>Assigned</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($results as $r): ?>
                    <tr>
                        <?php foreach ($r as $key => $val): ?>
                            <?php if ($key == "days_offset"): ?>
                                <?php if ($val >= 0): ?>
                                    <td><?php echo "Completed $val day(s)"; ?></td>
                                <?php else: ?>
                                    <td><?php echo "Overdue by " . abs($val) . " day(s)"; ?></td>
                                <?php endif; ?>

                            <?php else: ?>
                                <td><?php echo $val; ?></td>
                            <?php endif; ?>
                        <?php endforeach; ?>
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