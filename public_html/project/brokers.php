<?php
require(__DIR__ . "/../../partials/nav.php");
if (is_logged_in(true)) {
    error_log("Session data: " . var_export($_SESSION, true));
}

$allowed_columns = ["name", "rarity", "life", "attack", "defense", "power", "created"];
$sort = ["asc", "desc"];

$params = [];
// I need a two step query for this due to the relationship of Brokers and Stocks
// I want the limit to apply to the brokers and fetch the matched broker's stocks.

// Step 1: Get broker IDs only
$query = "SELECT b.id FROM `IT202-M25-Brokers` b WHERE 1=1";

// Filtering logic
if (count($_GET) > 0) {
    $name = se($_GET, "name", "", false);
    if (!empty($name)) {
        $query .= " AND name LIKE :name";
        $params[":name"] = "%$name%";
    }

    $rarity = se($_GET, "rarity", "", false);
    if (is_numeric($rarity)) {
        $query .= " AND rarity = :rarity";
        $params[":rarity"] = $rarity;
    }

    $column = se($_GET, "column", "", false);
    if (empty($column) || !in_array($column, $allowed_columns)) {
        $column = "created";
    }

    $order = se($_GET, "order", "", false);
    if (empty($order) || !in_array($order, $sort)) {
        $order = "desc";
    }

    $query .= " ORDER BY b.$column $order";
}
// outside of the $_GET check to always provide a limit
$limit = se($_GET, "limit", 10, false);
if (!empty($limit) && is_numeric($limit)) {
    if ($limit < 1 || $limit > 100) {
        $limit = 10;
    }
    $query .= " LIMIT :limit";
    $params[":limit"] = $limit;
}
// Execute broker query
$db = getDB();
$stmt = $db->prepare($query);
error_log("Broker Query: $query");
error_log("Params: " . var_export($params, true));

foreach ($params as $key => $val) {
    $type = match (true) {
        is_numeric($val) => PDO::PARAM_INT,
        is_bool($val) => PDO::PARAM_BOOL,
        is_null($val) => PDO::PARAM_NULL,
        default => PDO::PARAM_STR,
    };
    $stmt->bindValue($key, $val, $type);
}

$broker_ids = [];
try {
    $stmt->execute();
    $r = $stmt->fetchAll();
    if ($r) {
        // Map to flat array of IDs
        $broker_ids = array_map(fn($row) => $row["id"], $r);
    }
} catch (PDOException $e) {
    error_log("Error fetching brokers: " . var_export($e, true));
    flash("Unhandled error occurred", "danger");
}

// step 2
// Fetch each broker's stocks
// Map each broker's stocks
$results = [];
error_log("Broker Ids: " . var_export($broker_ids, true));
if ($broker_ids) {
    // Question marks are positional placeholders
    $in = str_repeat('?,', count($broker_ids) - 1) . '?';
    $query = "SELECT b.id, name, rarity, life, attack, defense, power, symbol, price, shares
        FROM `IT202-M25-Brokers` b
        LEFT JOIN `IT202-M25-BrokerStocks` bs ON b.id = bs.broker_id
        LEFT JOIN `IT202-M25-Stocks` s ON bs.stock_id = s.id
        WHERE b.id IN ($in)";
    // Sort logic
    if (count($_GET) > 0) {

        $column = se($_GET, "column", "", false);
        if (empty($column) || !in_array($column, $allowed_columns)) {
            $column = "created";
        }

        $order = se($_GET, "order", "", false);
        if (empty($order) || !in_array($order, $sort)) {
            $order = "desc";
        }

        $query .= " ORDER BY b.$column $order";
    }
    $stmt = $db->prepare($query);
    $stmt->execute($broker_ids);
    $brokers = $stmt->fetchAll();
    error_log("Raw Broker Data: " . var_export($brokers, true));

    // Aggregate
    foreach ($brokers as $row) {
        $id = $row["id"];
        if (!isset($results[$id])) {
            $results[$id] = [
                "broker" => [
                    "id" => $id,
                    "name" => $row["name"],
                    "rarity" => $row["rarity"],
                    "life" => $row["life"],
                    "attack" => $row["attack"],
                    "defense" => $row["defense"],
                    "power" => $row["power"]
                ],
                "stocks" => []
            ];
        }

        if (!empty($row["symbol"])) {
            $results[$id]["stocks"][] = [
                "symbol" => $row["symbol"],
                "price" => $row["price"],
                "shares" => $row["shares"]
            ];
        }
    }
    $results = array_values($results); // reindex for rendering
}


// Build filter form
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
        "type" => "number",
        "id" => "rarity",
        "name" => "rarity",
        "label" => "Rarity",
        "value" => se($_GET, "rarity", "", false),
        "rules" => ["min" => 0, "max" => 5]
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
    <h1>Brokers</h1>
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

    <?php if (count($results) == 0): ?>
        <p>No brokers found</p>
    <?php else: ?>
        <div class="row">
            <?php foreach ($results as $entry): ?>
                <div class="col">
                    <?php render_broker_card($entry); ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php require(__DIR__ . "/../../partials/flash.php"); ?>
