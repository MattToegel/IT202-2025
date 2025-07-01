<?php
require(__DIR__ . "/../../partials/nav.php");
if (is_logged_in(true)) {
    error_log("Session data: " . var_export($_SESSION, true));
}
$allowed_columns = ["symbol", "open", "low", "high", "price", "change_percent", "latest_trading_day", "volume"];
$sort = ["asc", "desc"];

$params = [];
$query = "SELECT id, symbol, open, low, high, price, change_percent, latest_trading_day, volume, is_api FROM `IT202-M25-Stocks`
WHERE 1=1";// used for easy append of other clauses
if(count($_GET)> 0){
    $symbol = se($_GET, "symbol", "", false);
    if(!empty($symbol)){
        $query .= " AND symbol like :symbol";
        $params[":symbol"] = "%$symbol%";
    }
    $latest_trading_day = se($_GET, "latest_trading_day", "", false);
    if(!empty($latest_trading_day)){
        $query .= " AND latest_trading_day >= :latest_trading_day";
        $params[":latest_trading_day"] = $latest_trading_day;
    }
    $column = se($_GET, "column", "", false);
    if(empty($column) || !in_array($column, $allowed_columns)){
        $column = "created";
    }
    $order = se($_GET, "order", "", false);
    if(empty($order) || !in_array($order, $sort)){
        $order = "desc";
    }
    // make sure values are trusted
    $query .= " ORDER BY $column $order";
    $limit = se($_GET, "limit", 10, false);
    if(!empty($limit) && is_numeric($limit)){
        if($limit < 1 || $limit > 100){
            $limit = 10;
        }   
        $query .= " LIMIT :limit";
        $params[":limit"] = $limit;
    }
}
$db = getDB();
$stmt = $db->prepare($query);
error_log("Query: " . $query);
error_log("Params: " . var_export($params, true));
foreach($params as $key=>$v){
    // determine PDOPAram type
    $type = match (true) {
        is_numeric($v)   => PDO::PARAM_INT,
        is_bool($v)  => PDO::PARAM_BOOL,
        is_null($v)  => PDO::PARAM_NULL,
        default          => PDO::PARAM_STR,
    };
    $stmt->bindValue("$key", $v,$type);
}
$results = [];
try {
    $stmt->execute();
    $r = $stmt->fetchAll();
    if ($r) {
        $results = $r;
    }
} catch (PDOException $e) {
    error_log("Error fetching stocks " . var_export($e, true));
    flash("Unhandled error occurred", "danger");
}
// TODO filter/sort (last resort if brokers aren't added in this lesson)

// form field for symbol
// form field date range for latest_trading_day
// form field for is_api
// form field for column names
// form field for asc/desc

$cols = array_map(function ($col) {
    return [$col => $col];
}, $allowed_columns);
array_unshift($cols, [""=>"Select Column"]);
$order = array_map(function ($col) {
    return [$col => $col];
}, $sort);
array_unshift($order, [""=>"Select Order"]);
$form = [
    [
        "type" => "text",
        "id" => "symbol",
        "name" => "symbol",
        "label" => "Stock Symbol",
        "value"=> se($_GET, "symbol", "", false),
    ],
    [
        "type" => "date",
        "id" => "latest_trading_day",
        "name" => "latest_trading_day",
        "label" => "Latest Trading Day",
        "value"=> se($_GET, "latest_trading_day", "", false),
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
        "type"=>"number",
        "id"=>"limit",
        "name"=>"limit",
        "label"=>"Limit",
        "value"=>se($_GET, "limit", "10", false),
        "rules"=>["min"=>1, "max"=>100]
    ]
]
?>
<div class="container-fluid">
    <h1>Home</h1>
    <div>
        <form>
            <div class="row">
            <?php foreach ($form as $field): ?>
                <div class="col">
                    <?php render_input($field); ?>
                </div>
            <?php endforeach; ?>
            </div>
            <?php render_button(["text" => "Search", "type" => "submit"]); ?>
            <!-- Uses `?` to remove all query params (normal reset doesn't work here
             because a regular reset "resets" back to the values the form loaded in with.
             Sticky forms will "reset" to what was last applied) -->
            <a href="?" class="btn btn-secondary">Reset</a>
        </form>
    </div>  
    <?php if (count($results) == 0) : ?>
        <p>No results to show</p>
    <?php else : ?>
        <div class="row">
            <?php foreach ($results as $stock): ?>
                <div class="col">
                    <?php render_stock_card($stock); ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
<?php
require(__DIR__ . "/../../partials/flash.php");
?>