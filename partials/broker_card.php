<?php
if (!isset($data["broker"]) || !isset($data["stocks"])) {
    error_log("Using Broker card without broker or stocks data");
    flash("Dev Alert: Broker card called without full data", "danger");
}
?>

<?php if (isset($data["broker"], $data["stocks"])) :
    $broker = $data["broker"];
    $stocks = $data["stocks"];


?>

    <div class="card mx-auto my-3 border-dark" style="width: 24rem;">
        <div class="card-body">
            <h5 class="card-title fw-bold text-center"><?php se($broker, "name", "Unknown Broker"); ?></h5>
            <h6 class="card-subtitle mb-2 text-muted">
                Rarity: <?php echo render_stars($broker["rarity"] ?? 0); ?>
            </h6>
            <?php if (isset($broker["username"])): ?>
                <h6 class="card-subtitle mb-2 text-muted">
                    Hired by: <?php se($broker, "username"); ?> <?php echo (get_user_id() == se($broker, "user_id", -1, false) ? "(You)" : ""); ?>
                </h6>
            <?php endif; ?>
            <div class="card-text">
                <ul class="list-group list-group-flush mb-2">
                    <li class="list-group-item">❤️ Life: <?php se($broker, "life", "?"); ?></li>
                    <li class="list-group-item">⚔️ Attack: <?php se($broker, "attack", "?"); ?></li>
                    <li class="list-group-item">🛡️ Defense: <?php se($broker, "defense", "?"); ?></li>
                    <li class="list-group-item">🔥 Power: <?php se($broker, "power", "?"); ?></li>
                </ul>
                <h6 class="mt-3">📈 Stocks:</h6>
                <ul class="list-group list-group-flush">
                    <?php foreach ($stocks as $stock) : ?>
                        <li class="list-group-item">
                            <strong><?php se($stock, "symbol", "???"); ?></strong>
                            – Qty: <?php se($stock, "shares", 0); ?>,
                            Price: $<?php se($stock, "price", "?"); ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <div class="card-footer text-muted text-center">
                <!-- link to view, delete-->
                <a href="<?php get_url("broker.php?id=" . se($broker, "id", -1, false), true); ?>" class="btn btn-primary mt-2">View Broker</a>
                <!-- delete if admin-->
                <?php if (has_role("Admin")) : ?>
                    <a href="<?php get_url("admin/delete_broker.php?id=" . se($broker, "id", -1, false), true); ?>" class="btn btn-danger mt-2">Disable</a>
                <?php endif; ?>
            </div>
            <?php if (!isset($broker["user_id"])): ?>
                <div class="card-body">
                    <form method="POST" action="<?php get_url("internal/hire.php", true); ?>">
                        <input type="hidden" name="broker_id" value="<?php se($broker, "id"); ?>">
                        <?php $cost = get_cost($broker);
                        render_button(["text" => "Hire ($cost)", "type" => "submit"]); ?>
                    </form>
                </div>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>