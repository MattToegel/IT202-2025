<?php if (!isset($result_stats)): ?>
    <p>No Result Data</p>
<?php else: ?>
    <div>Results: <?php se($result_stats["current"]); ?>/<?php se($result_stats["total"]); ?></div>
<?php endif; ?>