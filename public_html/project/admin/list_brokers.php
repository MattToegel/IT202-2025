<?php
//note we need to go up 1 more directory
require(__DIR__ . "/../../../partials/nav.php");

if (!has_role("Admin")) {
    flash("You don't have permission to view this page", "warning");
    die(header("Location: " . get_url("landing.php")));
}
store_current_route();

// handle toggle
if (isset($_POST["broker_id"])) {
    $broker_id = se($_POST, "broker_id", -1, false);
    if ($broker_id < 0) {
        flash("Invalid broker id", "danger");
        redirect(get_last_route());
    }
    $db = getDB();
    $stmt = $db->prepare("UPDATE `IT202-M25-Brokers` SET is_active = !is_active WHERE id = :id");
    try {
        $stmt->execute([":id" => $broker_id]);
        if ($stmt->rowCount() > 0) {
            flash("Toggled broker with id $broker_id", "success");
        } else {
            flash("No changes made, broker may not exist or already toggled", "warning");
        }
    } catch (PDOException $e) {
        flash("There was an error toggling the broker, please try again later", "danger");
        error_log("Error toggling broker with id $broker_id: " . var_export($e->errorInfo, true));
    }
}
// end handle toggle

$allowed_columns = ["name", "rarity", "life", "attack", "defense", "power", "created"];
$sort = ["asc", "desc"];

$params = [];
// I need a two step query for this due to the relationship of Brokers and Stocks
// I want the limit to apply to the brokers and fetch the matched broker's stocks.

// Step 1: Get broker IDs only
// Note: I can't join on stocks here otherwise it'll give me incorrect results
$from = " FROM `IT202-M25-Brokers` b 
LEFT JOIN `IT202-M25-UserBrokers` ub on b.id = ub.broker_id 
LEFT JOIN Users u on u.id = ub.user_id";
$query = "SELECT b.id, name, rarity, life, attack, defense, power, username, user_id, IF(b.is_active, 'active', 'inactive') as is_active, b.created";
$count = "SELECT count(b.id) as total";
$count_where = "";
// not filtering for is_active here since this is an admin page
$where = " WHERE 1=1";

// Filtering logic
if (count($_GET) > 0) {
    $name = se($_GET, "name", "", false);
    if (!empty($name)) {
        $where .= " AND name LIKE :name";
        $params[":name"] = "%$name%";
    }
    $username = se($_GET, "username", "", false);
    if (!empty($username)) {
        $where .= " AND u.username LIKE :username";
        $params[":username"] = "%$username%";
    }
    $rarity = se($_GET, "rarity", "", false);
    if (is_numeric($rarity)) {
        $where .= " AND rarity = :rarity";
        $params[":rarity"] = $rarity;
    }
    $status = se($_GET, "is_active", "", false);
    if ($status !== "") {
        $where .= " AND b.is_active = :is_active";
        $params[":is_active"] = $status === "1" ? 1 : 0;
    }

    $column = se($_GET, "column", "", false);
    if (empty($column) || !in_array($column, $allowed_columns)) {
        $column = "created";
    }

    $order = se($_GET, "order", "", false);
    if (empty($order) || !in_array($order, $sort)) {
        $order = "desc";
    }

    $where .= " ORDER BY b.$column $order";
}
// outside of the $_GET check to always provide a limit
$limit = se($_GET, "limit", 10, false);
if (!empty($limit) && is_numeric($limit)) {
    if ($limit < 1 || $limit > 100) {
        $limit = 10;
    }
    $count_where = $where; // count doesn't use limit
    $where .= " LIMIT :limit";
    // better practice to explicitly cast than to use is_numeric() for PDO binding
    $params[":limit"] = (int)$limit;
}
// Execute broker query
$results = selectAll("$query $from $where", $params);
$results = array_map(function ($row) {
    // Convert user_id to a link to the profile
    if (isset($row["user_id"])) {
        $url = get_url("profile.php?id=" . se($row, "user_id", -1, false));
        $username = se($row, "username", "N/A", false);
        $row["profile"] = "<a href=\"$url\">$username</a>";
    } else {
        $row["profile"] = "N/A";
    }
    return $row;
}, $results);
// Convert to render table
$table = [
    "data" => $results,
    "ignored_columns" => ["user_id"],
    "view_url" => get_url("broker.php"),
    //"edit_url" => get_url("admin/edit_broker.php"),
    "delete_url" => get_url("admin/delete_broker.php"),
    "post_self_form" => [
        "name" => "broker_id",
        "label" => "Toggle Active",
        "classes" => "btn btn-secondary"
    ],
    "html_columns" => ["profile"],
];


unset($params[":limit"]); // limit isn't used with the count query
// Execute count query
$count_results = selectAll("$count $from $count_where", $params, true)[0];
// transform result data for results_header.php
$result_stats = [
    "current" => count($results),
    "total" => $count_results["total"]
];

// Build filter form
$status = [
    ["" => "All"],
    ["1" => "Active"],
    ["0" => "Inactive"]
];
$cols = array_map(fn($col) => [$col => $col], $allowed_columns);
array_unshift($cols, ["" => "Select Column"]);

$order = array_map(fn($dir) => [$dir => $dir], $sort);
array_unshift($order, ["" => "Select Order"]);

$form = [
    [
        "type" => "text",
        "id" => "name",
        "name" => "name",
        "label" => "Broker Name",
        "value" => se($_GET, "name", "", false),
    ],
    [
        "type" => "text",
        "id" => "username",
        "name" => "username",
        "label" => "Username",
        "value" => se($_GET, "username", "", false),
    ],
    [
        "type" => "number",
        "id" => "rarity",
        "name" => "rarity",
        "label" => "Rarity",
        "value" => se($_GET, "rarity", "", false),
        "rules" => ["min" => 0, "max" => 5]
    ],
    [
        "type" => "select",
        "id" => "status",
        "name" => "is_active",
        "label" => "status",
        "options" => $status,
        "value" => se($_GET, "is_active", "", false),
    ],
    [
        "type" => "select",
        "id" => "column",
        "name" => "column",
        "label" => "Column",
        "options" => $cols,
        "value" => se($_GET, "column", "", false),
    ],
    [
        "type" => "select",
        "id" => "order",
        "name" => "order",
        "label" => "Order",
        "options" => $order,
        "value" => se($_GET, "order", "", false),
    ],
    [
        "type" => "number",
        "id" => "limit",
        "name" => "limit",
        "label" => "Limit",
        "value" => se($_GET, "limit", "10", false),
        "rules" => ["min" => 1, "max" => 100]
    ]
];
?>
<div class="container-fluid">
    <h1>Admin List Brokers</h1>
    <small>These brokers include hired and not hired results.</small>
    <form>
        <div class="row">
            <?php foreach ($form as $field): ?>
                <div class="col">
                    <?php render_input($field); ?>
                </div>
            <?php endforeach; ?>
        </div>
        <?php render_button(["text" => "Search", "type" => "submit"]); ?>
        <a href="?" class="btn btn-secondary">Reset</a>
    </form>
    <?php results_header($result_stats); ?>
    <?php if (count($results) == 0): ?>
        <p>No brokers found</p>
    <?php else: ?>
        <?php render_table($table); ?>
    <?php endif; ?>
</div>

<?php require(__DIR__ . "/../../../partials/footer.php"); ?>