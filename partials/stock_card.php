<?php
if (!isset($data)) {
    error_log("Using Stock card partial without data");
    flash("Dev Alert: Stock card called without data", "danger");
}
?>
<?php if (isset($data)) : ?>
    <div class="card mx-auto my-3" style="width: 20rem;">
        <div class="ratio ratio-1x1 d-flex justify-content-center  " style="height:64px">
            <img src="https://cdn-icons-png.flaticon.com/512/2331/2331949.png" class="img-fluid object-fit-contain " alt="Stock Icon">
        </div>
        <div class="card-body">
            <h5 class="card-title"><?php se($data, "symbol", "Unknown"); ?> Stock</h5>
            <div class="card-text">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item">Open: $<?php se($data, "open", "N/A"); ?></li>
                    <li class="list-group-item">Low: $<?php se($data, "low", "N/A"); ?></li>
                    <li class="list-group-item">High: $<?php se($data, "high", "N/A"); ?></li>
                    <li class="list-group-item">Current Price: $<?php se($data, "price", "N/A"); ?></li>
                    <li class="list-group-item">Change %: <?php se($data, "change_percent", "N/A"); ?>%</li>
                    <li class="list-group-item">Volume: <?php se($data, "volume", "N/A"); ?></li>
                    <li class="list-group-item">Date: <?php se($data, "latest_trading_day", "N/A"); ?></li>
                </ul>
            </div>
        </div>
    </div>
<?php endif; ?>