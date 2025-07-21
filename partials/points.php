<div id="original-points">Points: <?php echo get_my_stats()["points"] ?? "0" ?></div>

<script>
    // populate point slots
    let source = document.getElementById("original-points");
    if (source) {
        // find all placeholders
        let targets = document.getElementsByClassName("show-points");
        for (let target of targets) {
            // copy the content of "original-points" into this placeholder instance
            target.innerHTML = source.innerHTML;
        }
        source.remove(); // remove the original element to avoid duplication
    }
</script>