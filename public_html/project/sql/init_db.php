<style>
    table.db-output-table {
        border-collapse: collapse;
        width: 95%;
        max-width: 900px;
        margin: 1.5em 0 2em 0;
        font-size: 1rem;
        background: #fff;
    }

    table.db-output-table th,
    table.db-output-table td {
        border: 1px solid #ccc;
        padding: 8px 12px;
    }

    table.db-output-table th {
        background: #eee;
        font-weight: bold;
    }

    table.db-output-table tr:nth-child(even) {
        background: #fafafa;
    }

    .success {
        color: #008a00;
        font-weight: bold;
    }

    .warn {
        color: #e6b800;
        font-weight: bold;
    }

    .error {
        color: #c00;
        font-weight: bold;
    }

    details summary {
        cursor: pointer;
    }

    .db-output-table tbody tr:hover {
        background: #ffffe0;
    }

    @media (max-width: 600px) {

        table.db-output-table,
        table.db-output-table th,
        table.db-output-table td {
            font-size: 0.95em;
        }
    }
    .rate-limit-info {
    color: #888;
    font-size: 0.97em;
    margin: 1em 0;
}
.rate-limit-warning {
    color: #e6b800;
    font-weight: bold;
    margin: 1em 0;
}

</style>
<h1>Database Helper Tool</h1>
<h6>v2025.11.6</h6>
<details>
    <summary>Info (About the tool)</summary>
    <p>The scope of this tool is to help us separate our structural queries into separate files for better organization.</p>
    <p>This tools job is to attempt to read all of those files and determine which ones are needed to run against your database to synchronize the structure.</p>
    <p>This tool only works for queries that take zero parameters.</p>
    <p>It can be used to preload some data via inserts, but those queries <em>MUST</em> be crafted in such a way that you don't generate duplicates during each run.</p>
    <p>Files should be <a href="https://en.wikipedia.org/wiki/Idempotence">Idempotent</a></p>
</details>
<?php

$rl = rate_limit_check();
render_rate_limit_message($rl);
if ($rl['is_limited']) exit;

?>

<?php
# IMPORTANT: There should be no need to edit anything in this file
# Simply drop new structural .sql files into this directory then access this file in the browser
# It'll load all of the sql files and attempt to run them against the database
# Make sure you prefix the file names with at least a left padded 3 digit incrementing number (i.e., 001, 002)
# This ensures the files always run in the precise order required (order of execution is extremely important)
# Updated 06/11/2025

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once(__DIR__ . "/../../../lib/db.php");
$count = 0;

try {
    // Step 1: Gather files and pre-check for valid/invalid naming
    $files = glob(__DIR__ . "/*.sql");
    $rows = []; // will hold all display rows

    $db = null;
    $tables = [];
    $t = [];
    $valid_sql = [];

    foreach ($files as $filename) {
        $file = basename($filename);
        if (preg_match('/^\d{3}_/', $file)) {
            $valid_sql[$filename] = file_get_contents($filename);
        } else {
            // Mark as rejected for display later
            $rows[] = [
                'file' => $file,
                'status' => false,
                'reason' => "Filename does not begin with 3 numbers followed by an underscore. Ex: 001_example.sql"
            ];
        }
    }

    // Step 2: Database logic for valid files
    if (!empty($valid_sql)) {
        ksort($valid_sql);

        // Pull current table names from DB
        $db = getDB();
        $stmt = $db->prepare("show tables");
        $stmt->execute();
        $count++;
        $tables = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Flatten the tables array for easy lookup
        foreach ($tables as $row) {
            foreach ($row as $key => $value) {
                $t[] = $value;
            }
        }

        foreach ($valid_sql as $fname => $value) {
            $file = basename($fname);
            $lines = explode("(", $value, 2);
            $skipped = false;
            $skip_msg = "";
            $status = null;
            $error = null;

            // Extract table name for CREATE TABLE detection
            if (count($lines) > 0) {
                $line = $lines[0];
                $line = preg_replace('!\s+!', ' ', $line);
                $line = str_ireplace("create table", "", $line);
                $line = str_ireplace("if not exists", "", $line);
                $line = str_ireplace("`", "", $line);
                $line = trim($line);
                if ($line && in_array($line, $t)) {
                    // Already exists, mark as skipped
                    $rows[] = [
                        'file' => $file,
                        'status' => true,
                        'skipped' => true,
                        'skip_msg' => "Table already exists: $line",
                        'sql' => $value,
                        'error' => null
                    ];
                    continue;
                }
            }

            // Try running the SQL
            $stmt = $db->prepare($value);
            try {
                $result = $stmt->execute();
            } catch (PDOException $e) {
                // Intentionally ignored, errorInfo (below) will show it
            }
            $count++;
            $error = $stmt->errorInfo();

            $rows[] = [
                'file' => $file,
                'status' => $error[0] === "00000",
                'skipped' => false,
                'sql' => $value,
                'error' => $error
            ];
        }
    }
    // If there are no files at all or no valid SQL files
    $no_files = empty($files);
    $no_valid_files = empty($valid_sql);
} catch (Exception $e) {
    echo $e->getMessage();
    exit("Something went wrong");
}

function is_duplicate_warning($error)
{
    // MySQL error code 1062 is duplicate entry, 1060 is duplicate column, 1061 is duplicate key name, 1068 is multiple primary key, etc.
    if (!is_array($error)) return false;
    if (isset($error[1]) && in_array($error[1], [1060, 1061, 1062, 1068])) return true;
    if (isset($error[2]) && (
        stripos($error[2], "Duplicate entry") !== false ||
        stripos($error[2], "Duplicate column") !== false ||
        stripos($error[2], "Duplicate key") !== false ||
        stripos($error[2], "multiple primary key") !== false
    )) return true;
    return false;
}

?>


<?php if (isset($no_files) && $no_files): ?>
    <p>Didn't find any files, please check the directory/directory contents/permissions (note files must end in .sql)</p>
<?php elseif (isset($no_valid_files) && $no_valid_files): ?>
    <p>All files were rejected due to invalid filenames.</p>
<?php else: ?>
    <table class="db-output-table">
        <thead>
            <tr>
                <th>File</th>
                <th>Status</th>
                <th>Details</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($rows as $row): ?>
                <tr>
                    <td>
                        <?php if (isset($row['sql'])): ?>
                            <details>
                                <summary><?php echo htmlspecialchars($row['file']); ?> (Show SQL)</summary>
                                <pre><code><?php echo htmlspecialchars($row['sql']); ?></code></pre>
                            </details>
                        <?php else: ?>
                            <?php echo htmlspecialchars($row['file']); ?>
                        <?php endif; ?>
                    </td>
                    <td style="text-align:center">
                        <?php if (isset($row['status']) && $row['status']): ?>
                            <span class="success">&#10003;</span>
                        <?php elseif (!empty($row['error']) && is_duplicate_warning($row['error'])): ?>
                            <span class="warn">&#9888;</span>
                        <?php else: ?>
                            <span class="error">&#10007;</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if (isset($row['reason'])): ?>
                            <details>
                                <summary>Rejected file</summary>
                                <p class="error"><?php echo htmlspecialchars($row['reason']); ?></p>
                            </details>
                        <?php elseif (isset($row['skipped']) && $row['skipped']): ?>
                            <details>
                                <summary>Table already exists (skipped)</summary>
                                <p class="success"><?php echo htmlspecialchars($row['skip_msg']); ?></p>
                            </details>
                        <?php else: ?>
                            <details>
                                <summary>
                                    <?php
                                    if (!empty($row['error']) && is_duplicate_warning($row['error'])) {
                                        echo "Warning (duplicate)";
                                    } else {
                                        echo $row['status'] ? "Success" : "Error";
                                    }
                                    ?>
                                </summary>
                                <pre><?php echo htmlspecialchars(var_export($row['error'], true)); ?></pre>
                            </details>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>

    </table>
    <?php if (!empty($valid_sql)): ?>
        <p>Init complete, used approximately <?php echo $count; ?> db calls.</p>
    <?php endif; ?>
<?php endif; ?>

<?php
// page rate limit

function rate_limit_check($limit = 10, $seconds = 60) {
    session_start();
    if (!isset($_SESSION['db_tool_runs'])) { $_SESSION['db_tool_runs'] = []; }
    $_SESSION['db_tool_runs'] = array_filter(
        $_SESSION['db_tool_runs'],
        fn($ts) => $ts > time() - $seconds
    );

    $runs = count($_SESSION['db_tool_runs']) + 1;

    // If there were previous runs, use the oldest, else use now.
    if (!empty($_SESSION['db_tool_runs'])) {
        $oldest = min($_SESSION['db_tool_runs']);
        $reset_in = max(1, $seconds - (time() - $oldest));
    } else {
        $reset_in = $seconds;
    }

    $result = [
        'runs' => $runs,
        'limit' => $limit,
        'reset_in' => $reset_in,
        'is_limited' => $runs > $limit, // strict: 1..10 allowed, 11 blocks
    ];
    if (!$result['is_limited']) {
        $_SESSION['db_tool_runs'][] = time();
    }
    return $result;
}


function render_rate_limit_message($rl) {
    if ($rl['is_limited']) {
        echo '<div class="rate-limit-warning">
            &#9888; Rate limit: Please wait before running again. (' .
            $rl['runs'] . ' of ' . $rl['limit'] .
            ' runs in the last 60 seconds, resets in ' .
            $rl['reset_in'] . 's)
        </div>';
    } else {
        echo '<div class="rate-limit-info">
            <b>Notice:</b> This tool is rate-limited to <b>' . $rl['limit'] . ' runs per minute</b> per user session.<br>
            You have used <b>' . $rl['runs'] . '</b> of <b>' . $rl['limit'] . '</b> runs in the last 60 seconds.
            Limit resets in <b>' . $rl['reset_in'] . 's</b>.
        </div>';
    }
}
?>