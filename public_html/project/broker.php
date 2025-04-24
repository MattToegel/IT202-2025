<?php
require(__DIR__ . "/../../partials/nav.php");
/*if (is_logged_in(true)) {
    error_log("Session data: " . var_export($_SESSION, true));
}*/
$id = se($_GET, "id", -1, false);
if ($id < 1) {
    flash("Invalid broker", "danger");
    redirect($_SESSION["last"]?? "brokers.php");
}

$params = [];
$select = "SELECT b.id, name, rarity, life, attack, defense, power, symbol, price, shares, username, u.id as user_id";
$query = " FROM `IT202-S25-Brokers` b
LEFT JOIN `IT202-S25-BrokerStocks` bs ON b.id = bs.broker_id
LEFT JOIN `IT202-S25-Stocks` s ON bs.stock_id = s.id 
LEFT JOIN `IT202-S25-UserBrokers` ub on ub.broker_id = b.id
LEFT JOIN `Users` u on ub.user_id = u.id
WHERE 1=1";
$query .= " AND b.id = :broker_id";
$params[":broker_id"] = $id;

// Execute broker query
$db = getDB();
$stmt = $db->prepare($select . $query);
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

// Fetch each broker's stocks
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
                "power" => $row["power"],
                "username"=>$row["username"],
                "user_id"=>$row["user_id"]
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
$broker = $results[0]; // Get one

?>
<div class="container-fluid">
    <h1>Broker</h1>
    <?php if ($broker): ?>
        <?php render_broker_card($broker); ?>
    <?php else: ?>
        <p>No broker (big problem);</p>
    <?php endif; ?>
</div>

<?php require(__DIR__ . "/../../partials/footer.php"); ?>