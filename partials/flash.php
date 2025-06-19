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

<style>
    /* Define basic colors for standard bootstrap alert classes, scoped to .container#flash */
    /* This will be later moved into a separate CSS file in a future lesson */
    /* And one we add bootstrap, we will use the bootstrap classes instead of these */

    .container#flash {

        .alert {
            padding: 1rem;
            border-radius: 0.25rem;
            font-size: 1rem;
        }

        .alert-primary {
            background-color: #cfe2ff;
            color: #084298;
        }

        .alert-secondary {
            background-color: #e2e3e5;
            color: #41464b;
        }

        .alert-success {
            background-color: #d1e7dd;
            color: #0f5132;
        }

        .alert-danger {
            background-color: #f8d7da;
            color: #842029;
        }

        .alert-warning {
            background-color: #fff3cd;
            color: #664d03;
        }

        .alert-info {
            background-color: #cff4fc;
            color: #055160;
        }

        .alert-light {
            background-color: #fefefe;
            color: #636464;
        }

        .alert-dark {
            background-color: #212529;
            color: #fff;
        }
    }
</style>