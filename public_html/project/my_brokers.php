<?php
require(__DIR__ . "/../../partials/nav.php");
if (is_logged_in(true)) {
    error_log("Session data: " . var_export($_SESSION, true));
}
$current_path = $_SERVER["REQUEST_URI"] . "?" . http_build_query($_GET);
$_SESSION["last"] = $current_path;

$allowed_columns = ["name", "rarity", "life", "attack", "defense", "power", "created"];
$sort = ["asc", "desc"];

$params = [];
$select = "SELECT b.id,
  b.name,
  b.rarity,
  b.life,
  b.attack,
  b.defense,
  b.power,
  s.symbol,
  s.price,
  bs.shares";
$query = "
FROM `IT202-S25-Brokers` b
JOIN `IT202-S25-UserBrokers` ub ON b.id = ub.broker_id
JOIN `IT202-S25-BrokerStocks` bs ON b.id = bs.broker_id
JOIN `IT202-S25-Stocks` s ON bs.stock_id = s.id
WHERE 1=1";
$query .= " AND user_id = :user_id";
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

$params[":user_id"] = get_user_id();
$count_query = $query;//don't apply limit for count
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
$stmt = $db->prepare($select.$query);
error_log("Broker Query: $select.$query");
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

$brokers = [];
try {
    $stmt->execute();
    $r = $stmt->fetchAll();
    if ($r) {
        $brokers = $r;
    }
} catch (PDOException $e) {
    error_log("Error fetching brokers: " . var_export($e, true));
    flash("Unhandled error occurred", "danger");
}

// get count
$db = getDB();
$stmt = $db->prepare("SELECT COUNT(distinct b.id) as `count` $count_query");
error_log("Broker Query: SELECT COUNT(b.id) as `count` $count_query");
error_log("Params: " . var_export($params, true));
unset($params[":limit"]); // remove limit for count query
foreach ($params as $key => $val) {
    $type = match (true) {
        is_numeric($val) => PDO::PARAM_INT,
        is_bool($val) => PDO::PARAM_BOOL,
        is_null($val) => PDO::PARAM_NULL,
        default => PDO::PARAM_STR,
    };
    $stmt->bindValue($key, $val, $type);
}

$max = 0;
try {
    $stmt->execute();
    $r = $stmt->fetch();
    if ($r) {
        $max = se($r, "count", 0, false);
    }
} catch (PDOException $e) {
    error_log("Error fetching brokers: " . var_export($e, true));
    flash("Unhandled error occurred", "danger");
}

// Map each broker's stocks
$results = [];
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
    <?php if(has_role("Admin")):?>
        <a href="<?php echo get_url("admin/unassociate_user.php");?>" class="btn btn-danger">Remove All</a>
        <?php endif;?>
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
    <div>
        <?php $on_page = count($results);
        echo "Results: $on_page/$max";
        ?>
    </div>
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