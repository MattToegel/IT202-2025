<?php
//note we need to go up 1 more directory
require(__DIR__ . "/../../../partials/nav.php");

if (!has_role("Admin")) {
    flash("You don't have permission to view this page", "warning");
    die(header("Location: " . get_url("landing.php")));
}
?>

<?php
if (isset($_POST["rarity"])) {
    $rarity = $_POST["rarity"];
    $quote = [];
    try {
        $broker = generate_broker($rarity);
    } catch (Exception $e) {
        error_log("Error generating broker: " . var_export($e, true));
        flash("Error generating broker", "danger");
    }
}
?>
<div class="container-fluid">
    <h3>Generate Broker</h3>
    <form method="POST">
        <?php render_input(["type" => "text", "name" => "rarity", "id" => "rarity", "label" => "Rarity", "rules" => ["required" => true]]); ?>

        <?php render_button(["text" => "Generate", "type" => "submit"]); ?>
    </form>
    <div id="brokerData">
        <?php if (isset($broker)): ?>
            <h4>Broker Data</h4>
            <pre><?php var_export($broker); ?></pre>

        <?php endif; ?>
    </div>
</div>

<?php
//note we need to go up 1 more directory
require_once(__DIR__ . "/../../../partials/footer.php");
?>