<?php
/*put this at the bottom of the page so any templates
 populate the flash variable and then display at the proper timing*/
?>
<div class="container" id="flash">
    <?php $messages = getMessages(); ?>
    <?php if ($messages) : ?>
        <?php foreach ($messages as $msg) : ?>
            <!-- bootstrap classes will be utilized when we add bootstrap in a future lesson-->
            <div class="row justify-content-center">
                <!-- color matches bootstrap color classes-->
                <div class="alert alert-<?php se($msg, 'color', 'info'); ?>" role="alert">
                    <?php se($msg, "text", ""); ?>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<style>
    #flash {
        left: 50%;
        transform: translateX(-50%);
        width: auto;
        max-width: 600px;
        margin-left: auto;
        margin-right: auto;
        opacity: 0.9;
        z-index: 1000;
        position: fixed;
        top: 1rem;

        background-color: gainsboro;
    }

    #flash:empty,
    #flash:blank,
    #flash:not(:has(*)):not(:empty) {
        display: none;
    }
</style>