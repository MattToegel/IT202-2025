<?php
function echo_url($dest)
{
    if (!str_ends_with($dest, ".php")) {
        $dest .= ".php";
    }
    echo "/M4/todos/$dest";// If your folder name differs, update the path here
}
?>
<nav>

    <ul>
        <li><a href="<?php echo_url('create'); ?>">Create ToDo</a></li>
        <li><a href="<?php echo_url('pending'); ?>">Pending ToDos</a></li>
        <li><a href="<?php echo_url('completed'); ?>">Completed ToDos</a></li>
    </ul>

</nav>
<br>
<header>
    <!-- change UCID -->
    <span>mt85</span>
    &nbsp;|&nbsp;
    <?php
    date_default_timezone_set('America/New_York');
    echo date('l, F j, Y \a\t g:i A'); ?>
</header>
<style>
    header {
        text-align: center;
        padding: 1%;
    }

    nav {
        height: 50px;
        overflow-y: visible;
    }

    nav ul {
        list-style-type: none;
        margin: 0;
        padding: 0;

        background-color: #333;
        height: 50px;
        overflow-y: visible;
    }

    nav li {
        float: left;
        overflow-y: visible;
    }

    nav li a {
        display: block;
        color: white;
        text-align: center;
        padding: 14px 16px;
        text-decoration: none;
    }

    nav li span {
        text-transform: uppercase;
    }

    nav li a:hover {
        background-color: #111;
        font-size: 1.25em;
        border-radius: 5px;
        margin-top: 10px;
    }

    section {
        display: grid;
        place-items: center;
        width: 100%;
    }

    section>* {
        line-height: 1.25;

    }

    form input {
        margin-bottom: 8px;
        width: 100%;
    }

    table {
        border: 1px solid black;
        width: 80%;
    }

    td,
    th {
        border: 1px solid black;
        text-align: center;
        vertical-align: middle;
    }

    td form {
        display: inline;
        vertical-align: middle;
        height: 100%;
        margin: 0;
    }

    td form input {
        margin: 0;
    }

    label {
        display: block;
    }
</style>