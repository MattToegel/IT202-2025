<?php
require(__DIR__ . "/../../partials/nav.php");
/*if (is_logged_in(true)) {
    error_log("Session data: " . var_export($_SESSION, true));
}*/

$current_path = $_SERVER["REQUEST_URI"] . "?" . http_build_query($_GET);
$_SESSION["last"] = $current_path;




$allowed_columns = ["currency_symbol", "currency_name", "average", "date", "created"];
$sort = ["asc", "desc"];

$params = [];
$select = "SELECT c.id, `currency_symbol`, `currency_name`, `average`, `date`, `username`, u.id as user_id,
(select count(uc2.user_id) from `IT202-S25-UserCrypto` uc2 where uc2.crypto_id =  c.id) as `count`";
$query = " FROM `IT202-S25-Crypto` as c
JOIN `IT202-S25-UserCrypto` uc on uc.crypto_id = c.id
JOIN `Users` u on uc.user_id = u.id
WHERE 1=1";
$count_query = "";
// Filtering logic
if (count($_GET) > 0) {
    $symbol = se($_GET, "currency_symbol", "", false);
    if (!empty($symbol)) {
        $symbol = strtoupper($symbol);
        $query .= " AND currency_symbol = :symbol";
        $params[":symbol"] = "$symbol";
    }
    $name = se($_GET, "currency_name", "", false);
    if (!empty($name)) {
        $query .= " AND currency_name LIKE :name";
        $params[":name"] = "%$name%";
    }
    $name = se($_GET, "username", "", false);
    if (!empty($name)) {
        $query .= " AND username LIKE :name";
        $params[":name"] = "%$name%";
    }
    $date = se($_GET, "date", "", false);
    if (!empty($date)) {
        $query .= " AND date >= :date";
        $params[":date"] = "$date";
    }

    $column = se($_GET, "column", "", false);
    if (empty($column) || !in_array($column, $allowed_columns)) {
        $column = "created";
    }

    $order = se($_GET, "order", "", false);
    if (empty($order) || !in_array($order, $sort)) {
        $order = "desc";
    }

    $query .= " ORDER BY c.$column $order";
}
error_log(var_export($_SERVER, true));
$count_query = $query; //don't apply limit for count
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
$stmt = $db->prepare($select . $query);
error_log("Crypto Query: $select$query");
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

$crypto = [];
try {
    $stmt->execute();
    $r = $stmt->fetchAll();
    if ($r) {
        $crypto = $r;
        foreach($crypto as $index=>$value){
           
            if(isset($value["username"])){
                $url = get_url("profile.php") . "?id=" . ($value["user_id"]??-1);
                $username = $value["username"] ?? "unknown";
                $nv = "<a href=\"$url\">$username</a>";
                $value["username"] = $nv;// doesn't work
                $crypto[$index]["username"] = $nv; //works
                unset($crypto[$index]["user_id"]);
            }
            error_log("$index =>".var_export($value,true));
        }
    }
} catch (PDOException $e) {
    error_log("Error fetching crypto: " . var_export($e, true));
    flash("Unhandled error occurred", "danger");
}

// get count
$db = getDB();
$stmt = $db->prepare("SELECT COUNT(c.id) as `count` $count_query");
error_log("Crypto Query: SELECT COUNT(c.id) as `count` $count_query");
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
    error_log("Error fetching crypto: " . var_export($e, true));
    flash("Unhandled error occurred", "danger");
}

// Build filter form
$cols = array_map(fn($col) => [$col => $col], $allowed_columns);
array_unshift($cols, ["" => "Select Column"]);

$order = array_map(fn($dir) => [$dir => $dir], $sort);
array_unshift($order, ["" => "Select Order"]);

$form = [
    [
        "type" => "text",
        "id" => "currency_name",
        "name" => "currency_name",
        "label" => "Crypto Name",
        "value" => se($_GET, "currency_name", "", false),
    ],
    [
        "type" => "text",
        "id" => "currency_symbol",
        "name" => "currency_symbol",
        "label" => "Crypto Symbol",
        "value" => se($_GET, "currency_symbol", "", false),
    ],
    [
        "type" => "date",
        "id" => "date",
        "name" => "date",
        "label" => "date",
        "value" => se($_GET, "date", "", false),
    ],
    [
        "type" => "text",
        "id" => "username",
        "name" => "username",
        "label" => "Username",
        "value" => se($_GET, "username", "", false),
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
    <h1>Crypto</h1>

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
        <?php $on_page = count($crypto);
        echo "Results: $on_page/$max";
        ?>
    </div>
    <?php
    $table = ["data" => $crypto];
    if (has_role("Admin")) {
        $table["edit_url"] = get_url("admin/edit_crypto.php");
        $table["delete_url"] = get_url("admin/delete_crypto.php");
    }
    $table["view_url"] = get_url("entry.php");
    $table["ignored_columns"] = ["id", "user_id"];
    $table["html_columns"] = ["username"];
    $table["fav_url"] = get_url("api/toggle_fav.php");
    render_table($table);
    ?>
</div>

<?php require(__DIR__ . "/../../partials/footer.php"); ?>