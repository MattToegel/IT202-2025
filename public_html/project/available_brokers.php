<?php
require(__DIR__ . "/../../partials/nav.php");
if (is_logged_in(true)) {
    error_log("Session data: " . var_export($_SESSION, true));
}
store_current_route();
$allowed_columns = ["name", "rarity", "life", "attack", "defense", "power", "created"];
$sort = ["asc", "desc"];

$params = [];
// I need a two step query for this due to the relationship of Brokers and Stocks
// I want the limit to apply to the brokers and fetch the matched broker's stocks.

// Step 1: Get broker IDs only
// Note: I can't join on stocks here otherwise it'll give me incorrect results
$from = " FROM `IT202-M25-Brokers` b";
$query = "SELECT b.id";
$count = "SELECT count(b.id) as total";
$count_where = "";
// filter for soft delete
$where = " WHERE 1=1 AND b.is_active = 1 AND NOT EXISTS (select id from `IT202-M25-UserBrokers` ub WHERE ub.broker_id = b.id)";

// Filtering logic
if (count($_GET) > 0) {
    $name = se($_GET, "name", "", false);
    if (!empty($name)) {
        $where .= " AND name LIKE :name";
        $params[":name"] = "%$name%";
    }

    $rarity = se($_GET, "rarity", "", false);
    if (is_numeric($rarity)) {
        $where .= " AND rarity = :rarity";
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
$broker_ids = selectAll("$query $from $where", $params);
if ($broker_ids) {
    $broker_ids = array_map(fn($row) => $row["id"], $broker_ids);
}
error_log("Broker Ids: " . var_export($broker_ids, true));

// step 2
$results = [];
if ($broker_ids) {
    // Question marks are positional placeholders
    $in = str_repeat('?,', count($broker_ids) - 1) . '?';
    $query = "SELECT b.id, name, rarity, life, attack, defense, power, symbol, price, shares
        FROM `IT202-M25-Brokers` b
        JOIN `IT202-M25-BrokerStocks` bs ON b.id = bs.broker_id
        JOIN `IT202-M25-Stocks` s ON bs.stock_id = s.id
        WHERE b.id IN ($in)";
    // Fetch each broker's stocks
    $brokers = selectAll($query, $broker_ids);
    // Map each broker's stocks
    $results = aggregate_broker_data($brokers);
}
unset($params[":limit"]); // limit isn't used with the count query
// Execute count query
$count_results = selectAll("$count $from $count_where", $params, true)[0];
// transform result data for results_header.php
$result_stats = [
    "current" => count($results),
    "total" => $count_results["total"]
];

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
    <h1>Available Brokers</h1>
    <small>These brokers are not hired by anyone.</small>
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
        <div class="row">
            <?php foreach ($results as $entry): ?>
                <div class="col">
                    <?php render_broker_card($entry); ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php require(__DIR__ . "/../../partials/footer.php"); ?>
