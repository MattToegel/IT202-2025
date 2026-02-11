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
            <?php if (isset($broker["username"])): ?>
                <h6 class="card-subtitle mb-2 text-muted">
                    Hired By: <a href="<?php echo get_url("profile.php?id=") . $broker["user_id"];?>"><?php echo $broker["username"]; ?></a>
                </h6>
            <?php endif; ?>
            <h6 class="card-subtitle mb-2 text-muted">
                Rarity: <?php echo render_stars($broker["rarity"] ?? 0); ?>
            </h6>
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
            <div class="card-footer">
                <a class="btn btn-primary" href="<?php echo get_url("broker.php?id=") . se($broker, "id", -1, false); ?>">View</a>
                <?php if (has_role("Admin")): ?>
                    <a class="btn btn-secondary" href="<?php echo get_url("admin/edit_broker.php?id=") . se($broker, "id", -1, false); ?>">Edit</a>
                <?php endif; ?>
                <?php if (has_role("Admin")): ?>
                    <a class="btn btn-danger" href="<?php echo get_url("admin/delete_broker.php?id=") . se($broker, "id", -1, false); ?>">Delete</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
<?php endif; ?>