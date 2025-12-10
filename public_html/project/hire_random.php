<?php
require(__DIR__ . "/../../partials/nav.php");
if (is_logged_in(true)) {
    error_log("Session data: " . var_export($_SESSION, true));
}
$broker = [];
if (isset($_POST["hire"])) {
    // normally you'd check affordability first
    // but I'll leverage failures to create unassigned brokers
    error_log("Generating broker");
    $broker = generate_broker();
    if ($broker) {
        $broker["user_id"] = get_user_id();
        hire(get_user_id(), $broker["id"], 100);
    }
}
?>
<div class="container-fluid">
    <h3>Hire Random Broker</h3>
    <div>
        <form method="POST">
            <input type="hidden" name="hire" value="true" />
            <?php render_button(["text" => "Hire (100 pts)", "type" => "submit"]); ?>
        </form>
    </div>
    <div id="brokerData">
        <?php if (isset($broker) && !empty($broker)): ?>
            <h4>Broker Data</h4>
            <?php
            $stocks = $broker["stocks"];
            $data = [
                "broker" => $broker,
                "stocks" => $stocks,
            ];
            render_broker_card($data); ?>

        <?php endif; ?>
    </div>
</div>
<?php
require(__DIR__ . "/../../partials/footer.php");
?>