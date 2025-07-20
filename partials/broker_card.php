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
        </div>
    </div>
<?php endif; ?>