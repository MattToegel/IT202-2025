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
        // check affordability (exception thrown on failure)
        $purchased = false;
        try {
            change_points(get_user_id(), -100);
            $purchased = true;
        } catch (Exception $e) {
            error_log("Error changing points: " . var_export($e, true));
            flash("Error hiring broker", "danger");
        }
        if ($purchased) {
            $db = getDB();
            // insert into IT202-S25-UserBrokers
            $query = "INSERT INTO `IT202-M25-UserBrokers` (user_id, broker_id) VALUES (:user_id, :broker_id)";
            $params = [":user_id" => get_user_id(), ":broker_id" => $broker["id"]];
            try {
                $stmt = $db->prepare($query);
                $stmt->execute($params);
                flash("Successfully hired broker", "success");
            } catch (PDOException $e) {
                error_log("Error inserting user broker " . var_export($e, true));
                try {
                    change_points(get_user_id(), 100);
                    flash("Error hiring broker, points refunded", "danger");
                } catch (Exception $e) {
                    error_log("Error refunding points " . var_export($e, true));
                }
            }
        }
    }
}
?>
<div class="container-fluid">
    <h3>Hire Broker</h3>
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