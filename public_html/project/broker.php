<?php
require(__DIR__ . "/../../partials/nav.php");
if (is_logged_in(true)) {
    error_log("Session data: " . var_export($_SESSION, true));
}

$broker_id = se($_GET, "id", -1, false);
if ($broker_id <= 0) {
    flash("Invalid broker ID", "warning");
    redirect(get_last_route());
}
$query = "SELECT b.id, name, rarity, life, attack, defense, power, symbol, price, shares, username, user_id
        FROM `IT202-M25-Brokers` b
        LEFT JOIN `IT202-M25-BrokerStocks` bs ON b.id = bs.broker_id
        LEFT JOIN `IT202-M25-Stocks` s ON bs.stock_id = s.id
        LEFT JOIN `IT202-M25-UserBrokers` ub on b.id = ub.broker_id
        LEFT JOIN `Users` u on u.id = ub.user_id
        WHERE b.id = :id AND b.is_active = 1";

$broker = selectAll($query, [":id" => $broker_id], false);
// selectall returns an array (many)
if ($broker) {
    $broker = aggregate_broker_data($broker);
    $broker = $broker[0] ?? null; // extract single broker from array of one broker
}
if (!$broker) {
    flash("Broker not found", "warning");
    redirect(get_last_route());
}
?>
<div class="container-fluid">
    <h1>Broker: <?php se($broker["broker"], "name", "N/A"); ?></h1>
    <a href="<?php get_last_route(true); ?>" class="btn btn-secondary mb-2">Back</a>
    <?php if (!$broker): ?>
        <p>Broker not found</p>
    <?php else: ?>
        <div class="row">
            <div class="col">
                <?php render_broker_card($broker); ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php require(__DIR__ . "/../../partials/footer.php"); ?>