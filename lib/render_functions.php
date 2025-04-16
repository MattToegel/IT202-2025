<?php
/**
 * Render functions for various HTML components.
 * Wraps `include()` statements allowing easy reuse of HTML components.
 * The $data variable becomes available to the content inside of the included php file.
 */


function render_input($data = array())
{
    include(__dir__ . "/../partials/input_field.php");
}

function render_button($data = array())
{
    include(__DIR__ . "/../partials/button.php");
}

function render_table($data = array())
{
    include(__DIR__ . "/../partials/table.php");
}

function render_stock_card($data = array())
{
    include(__DIR__ . "/../partials/stock_card.php");
}